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
        if ('be_welcome' === $template) {
            $entries = ActiveTimesModel::getAllEntries();

            if ($entries) {
                $monthArray = $this->constructMonthArray($entries);
            }
            if ($monthArray) {
                $html = $this->constructHTML($monthArray);
            }

            // Search for last </div>
            $position = strrpos($buffer, '</div>');

            // Insert new table before last </div>
            $buffer = substr_replace($buffer, $html, $position, 0);
        }

        return $buffer;
    }

    // Adds the times for each user to the corresponding month
    public function constructMonthArray($entries)
    {
        // Get all enabled users
        $userArray = UserModel::findByDisable(0)->fetchAll();

        $months = [
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

        $currentYear = date('Y');
        $lastYear = $currentYear - 1;

        foreach ($userArray as $user) {
            // Initialise each user in each month for current year
            $monthArray[$currentYear][$months[12]][$user['username']] = 0;
            $monthArray[$currentYear][$months[11]][$user['username']] = 0;
            $monthArray[$currentYear][$months[10]][$user['username']] = 0;
            $monthArray[$currentYear][$months[9]][$user['username']] = 0;
            $monthArray[$currentYear][$months[8]][$user['username']] = 0;
            $monthArray[$currentYear][$months[7]][$user['username']] = 0;
            $monthArray[$currentYear][$months[6]][$user['username']] = 0;
            $monthArray[$currentYear][$months[5]][$user['username']] = 0;
            $monthArray[$currentYear][$months[4]][$user['username']] = 0;
            $monthArray[$currentYear][$months[3]][$user['username']] = 0;
            $monthArray[$currentYear][$months[2]][$user['username']] = 0;
            $monthArray[$currentYear][$months[1]][$user['username']] = 0;

            // Initialise each user in each month for last year
            $monthArray[$lastYear][$months[12]][$user['username']] = 0;
            $monthArray[$lastYear][$months[11]][$user['username']] = 0;
            $monthArray[$lastYear][$months[10]][$user['username']] = 0;
            $monthArray[$lastYear][$months[9]][$user['username']] = 0;
            $monthArray[$lastYear][$months[8]][$user['username']] = 0;
            $monthArray[$lastYear][$months[7]][$user['username']] = 0;
            $monthArray[$lastYear][$months[6]][$user['username']] = 0;
            $monthArray[$lastYear][$months[5]][$user['username']] = 0;
            $monthArray[$lastYear][$months[4]][$user['username']] = 0;
            $monthArray[$lastYear][$months[3]][$user['username']] = 0;
            $monthArray[$lastYear][$months[2]][$user['username']] = 0;
            $monthArray[$lastYear][$months[1]][$user['username']] = 0;

            foreach ($entries as $entry) {
                if ($entry['username']) {
                    // Add the times to the months
                    $monthArray[$entry['year']][$months[$entry['month']]][$entry['username']] += (int) $entry['length'];
                }
            }
        }

        return $monthArray;
    }

    // Constructs the html to insert to the template
    public function constructHTML($monthArray)
    {
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
                            $html .= '<table class="tl_listing"><thead><tr><th class="tl_folder_tlist" colspan="2">' . $month . ' ' . $year . '</th></tr></thead><tbody><tr class="toggle_select hover-row odd"><td class="tl_file_list" style="width:33.33%;"><strong>Benutzername</strong></td><td class="tl_file_list"><strong>Zeit</strong></td></tr>';
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
