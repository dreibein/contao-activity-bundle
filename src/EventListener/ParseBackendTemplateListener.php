<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\EventListener;

use Contao\ActivityBundle\Model\ActiveTimesModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\UserModel;

/**
 * @Hook("parseBackendTemplate")
 */
class ParseBackendTemplateListener
{
    public function __invoke(string $buffer, string $template): string
    {
        if ('be_welcome' !== $template) {
            return $buffer;
        }

        $monthArray = $this->constructMonthArray();

        if (null === $monthArray) {
            return $buffer;
        }

        $html = $this->constructHTML($monthArray);

        // Search for last </div>
        $position = strrpos($buffer, '</div>');

        // Insert new table before last </div>
        if (false !== $position) {
            $buffer = substr_replace($buffer, $html, $position, 0);
        }

        return $buffer;
    }

    // Adds the times for each user to the corresponding month
    public function constructMonthArray()
    {
        // Get all enabled users
        /** @var UserModel $userArray */
        $userArray = UserModel::findByDisable(0);
        if (null === $userArray) {
            return null;
        }

        /** @var ActiveTimesModel $entries */
        $entries = ActiveTimesModel::getAllEntries();
        if (null === $entries) {
            return null;
        }

        $currentYear = date('Y');
        $lastYear = $currentYear - 1;

        foreach ($userArray as $user) {
            // Initialise each user in each month for current year
            // Initialise each user in each month for last year
            for ($i = 12; $i >= 1; --$i) {
                $monthArray[$currentYear][$i][$user->username] = 0;
                $monthArray[$lastYear][$i][$user->username] = 0;
            }

            foreach ($entries as $entry) {
                if ($entry->username) {
                    // Add the times to the months
                    $monthArray[$entry->year][$entry->month][$entry->username] += (int) $entry->length;
                }
            }
        }

        return $monthArray ?? null;
    }

    // Constructs the html to insert to the template
    public function constructHTML($monthArray)
    {
        $monthNames = [
            1 => 'Januar',
            2 => 'Februar',
            3 => 'März',
            4 => 'April',
            5 => 'Mai',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'August',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Dezember',
        ];

        $html = '<div class="tl_listing_container list_view"><br><h2>Übersicht der Bearbeitungszeit aller Nutzer</h2><br>';
        foreach ($monthArray as $year => $months) {
            foreach ($months as $month => $users) {
                // Count us used for even and odd lines in table
                $count = 1;

                // Is true if header is to be printed
                $headerPrinted = false;

                foreach ($users as $username => $time) {
                    if ($time) {
                        if (!$headerPrinted) {
                            $headerPrinted = true;

                            // Table header is constructed
                            $html .= '<table class="tl_listing"><thead><tr><th class="tl_folder_tlist" colspan="2">' . $monthNames[$month] . ' ' . $year . '</th></tr></thead><tbody><tr class="toggle_select hover-row odd"><td class="tl_file_list" style="width:33.33%;"><strong>Benutzername</strong></td><td class="tl_file_list"><strong>Zeit</strong></td></tr>';
                        }

                        // Set lines to even / odd for styling
                        if (1 === $count % 2) {
                            $evenOdd = ' even';
                        } else {
                            $evenOdd = ' odd';
                        }

                        // Calculate time spent in hours and minutes
                        $minutes = (int) ($time / 60) % 60;
                        $hours = (int) ($time / 3600);

                        // Table line for each user is constructed
                        $html .= '<tr class="toggle_select hover-row' . $evenOdd . '"><td class="tl_file_list">' . $username . '</td><td class="tl_file_list">' . $hours . ' Stunde(n) ' . $minutes . ' Minute(n) ' . '</td></tr>';
                        ++$count;
                    }
                }
                if ($headerPrinted) {
                    $html .= '</tbody></table>';
                }
            }
        }

        return $html .= '</div>';
    }
}
