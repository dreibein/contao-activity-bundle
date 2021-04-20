<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\EventListener;

use Contao\ActivityBundle\Repository\ActiveTimesRepository;
use Contao\UserModel;
use Safe\DateTimeImmutable;

class ParseBackendTemplateListener
{
    private ActiveTimesRepository $activeTimesRepository;

    /**
     * ParseBackendTemplateListener constructor.
     *
     * @param ActiveTimesRepository $activeTimesRepository
     */
    public function __construct(ActiveTimesRepository $activeTimesRepository)
    {
        $this->activeTimesRepository = $activeTimesRepository;
    }

    /**
     * Extend the be_welcome template and add a user statistic to it.
     *
     * @param string $buffer
     * @param string $template
     *
     * @return string
     */
    public function onParseTemplate(string $buffer, string $template): string
    {
        // check if the template is be_welcome
        if ('be_welcome' !== $template) {
            return $buffer;
        }

        // search for the last </div> in the buffer
        $position = strrpos($buffer, '</div>');

        if (false === $position) {
            return $buffer;
        }

        // create statistic data
        $monthArray = $this->collectStatisticData();

        if (0 === \count($monthArray)) {
            return $buffer;
        }

        $html = $this->generateHtml($monthArray);

        // insert new table before last </div>
        return substr_replace($buffer, $html, $position, 0);
    }

    /**
     * Adds the times for each user to the corresponding month.
     *
     * @return array
     */
    private function collectStatisticData(): array
    {
        // get all active users
        $users = UserModel::findByDisable(0);
        if (null === $users) {
            return [];
        }

        // get all time activities
        try {
            $entries = $this->activeTimesRepository->findAllForCurrentYear();
        } catch (\Exception $e) {
            return [];
        }

        $statistic = [];
        $date = new DateTimeImmutable();
        $year = $date->format('Y');
        $lastYear = $year - 1;

        /** @var UserModel $user */
        foreach ($users as $user) {
            // Initialise each user in each month for current year
            // Initialise each user in each month for last year
            for ($i = 12; $i >= 1; --$i) {
                $statistic[$year][$i][$user->username] = 0;
                $statistic[$lastYear][$i][$user->username] = 0;
            }

            // Fill the array with values
            foreach ($entries as $entry) {
                if ($entry->getUsername() === $user->username) {
                    // Add the times to the months
                    $statistic[$entry->getYear()][$entry->getMonth()][$entry->getUsername()] += $entry->getLength();
                }
            }
        }

        return $statistic;
    }

    /**
     * Construct the html to insert to the template.
     *
     * @param array $monthArray
     *
     * @return string
     */
    private function generateHtml(array $monthArray): string
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
                    if ($time >= 60) {
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

        return $html . '</div>';
    }
}
