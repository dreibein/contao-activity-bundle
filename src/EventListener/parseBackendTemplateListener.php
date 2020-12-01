<?php


namespace Contao\ActivityBundle\EventListener;

use Contao\ActiveTimesModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\ActivityBundle\Model\ActiveTimesModel as ParseActiveTimesModel;
use Contao\Database;
use Contao\UserModel;


/**
 * @Hook("parseBackendTemplate")
 */
class parseBackendTemplateListener
{
    public function __invoke(string $buffer, string $template): string
    {

        if ($template === 'be_welcome') {

            $entries = ParseActiveTimesModel::getAllEntries();

            $year = date('Y');
            $lastYear = $year - 1;

            $monthArray = $this->constructMonthArray($entries);
            $html = $this->constructHTML($monthArray);
            $charCount = strlen($buffer);

            // SEARCH FOR LAST </DIV>
            $position = strrpos($buffer,'</div>');

            // INSERTS NEW TABLE BEFORE LAST </DIV>
            $buffer = substr_replace($buffer,$html,$position,0);
        }

        return $buffer;
    }

    // ADDS THE TIMES FOR EACH USER TO THE CORRESPONDING MONTH
    public function constructMonthArray ($entries) {

        // GET ALL ENABLED USERS
        $objDatabase = Database::getInstance();
        $sqlGetActiveUsers = "SELECT username FROM tl_user WHERE disable != 1";
        $objResult = $objDatabase->prepare($sqlGetActiveUsers)->execute();
        $userArray = $objResult->fetchAllAssoc();

        // INITIALISE MONTH NAMES
        $monthArray = [
            'Januar' => [],
            'Februar' => [],
            'März' => [],
            'April' => [],
            'Mai' => [],
            'Juni' => [],
            'Juli' => [],
            'August' => [],
            'September' => [],
            'Oktober' => [],
            'November' => [],
            'Dezember' => []
        ];

        $currentYear = date('Y');
        $lastYear = $currentYear - 1;

        foreach ($userArray as $user) {

            // INITIALISE EACH USER IN EACH MONTH FOR CURRENT YEAR
            $monthArray[$currentYear]['Dezember'][$user['username']] = 0;
            $monthArray[$currentYear]['November'][$user['username']] = 0;
            $monthArray[$currentYear]['Oktober'][$user['username']] = 0;
            $monthArray[$currentYear]['September'][$user['username']] = 0;
            $monthArray[$currentYear]['August'][$user['username']] = 0;
            $monthArray[$currentYear]['Juli'][$user['username']] = 0;
            $monthArray[$currentYear]['Juni'][$user['username']] = 0;
            $monthArray[$currentYear]['Mai'][$user['username']] = 0;
            $monthArray[$currentYear]['April'][$user['username']] = 0;
            $monthArray[$currentYear]['März'][$user['username']] = 0;
            $monthArray[$currentYear]['Februar'][$user['username']] = 0;
            $monthArray[$currentYear]['Januar'][$user['username']] = 0;

            // INITIALISE EACH USER IN EACH MONTH FOR LAST YEAR
            $monthArray[$lastYear]['Dezember'][$user['username']] = 0;
            $monthArray[$lastYear]['November'][$user['username']] = 0;
            $monthArray[$lastYear]['Oktober'][$user['username']] = 0;
            $monthArray[$lastYear]['September'][$user['username']] = 0;
            $monthArray[$lastYear]['August'][$user['username']] = 0;
            $monthArray[$lastYear]['Juli'][$user['username']] = 0;
            $monthArray[$lastYear]['Juni'][$user['username']] = 0;
            $monthArray[$lastYear]['Mai'][$user['username']] = 0;
            $monthArray[$lastYear]['April'][$user['username']] = 0;
            $monthArray[$lastYear]['März'][$user['username']] = 0;
            $monthArray[$lastYear]['Februar'][$user['username']] = 0;
            $monthArray[$lastYear]['Januar'][$user['username']] = 0;

            foreach ($entries as $entry) {
                if ($entry['username']) {

                    // ADD THE TIMES TO THE MONTH
                    switch ($entry['month']) {
                        case 1:
                            $monthArray[$entry['year']]['Januar'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 2:
                            $monthArray[$entry['year']]['Februar'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 3:
                            $monthArray[$entry['year']]['März'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 4:
                            $monthArray[$entry['year']]['April'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 5:
                            $monthArray[$entry['year']]['Mai'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 6:
                            $monthArray[$entry['year']]['Juni'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 7:
                            $monthArray[$entry['year']]['Juli'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 8:
                            $monthArray[$entry['year']]['August'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 9:
                            $monthArray[$entry['year']]['September'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 10:
                            $monthArray[$entry['year']]['Oktober'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 11:
                            $monthArray[$entry['year']]['November'][$entry['username']] += (int)$entry['length'];
                            break;
                        case 12:
                            $monthArray[$entry['year']]['Dezember'][$entry['username']] += (int)$entry['length'];
                            break;
                    }
                }
            }
        }
        return $monthArray;
    }

    // CONSTRUCTS THE HTML TO INSERT TO THE TEMPLATE
    public function constructHTML ($monthArray)
    {
        $html = '<div class="tl_listing_container list_view"><br><h2>Übersicht der Bearbeitungszeit aller Nutzer</h2><br>';
        foreach ($monthArray as $year => $months) {
            foreach ($months as $month => $users) {

                // COUNT IS USED FOR EVEN AND ODD LINES IN TABLE
                $count = 1;

                // IS TRUE IF HEADER IS TO BE PRINTED
                $headerPrinted = false;

                foreach ($users as $username => $time) {
                    if ($time) {
                        if (!$headerPrinted) {
                            $headerPrinted = true;

                            // TABLE HEADER IS CONSTRUCTED
                            $html .= '<table class="tl_listing"><tr><td class="tl_folder_tlist" colspan="2">' . $month . ' ' . $year . '</td></tr><tbody><tr class="toggle_select hover-row odd"><td class="tl_file_list"><strong>Benutzername</strong></td><td class="tl_file_list"><strong>Zeit</strong></td></tr>';
                        }

                        $count % 2 === 1 ? $evenOdd = ' even' : $evenOdd = ' odd';

                        // TABLE LINE FOR EACH USER IS CONSTRUCTED
                        $html .= '<tr class="toggle_select hover-row' . $evenOdd . '"><td class="tl_file_list">' . $username . '</td><td class="tl_file_list">' . gmdate('z', $time) . ' Tag(e) ' . gmdate('H:i:s', $time) . '</td></tr>';
                        $count++;
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
