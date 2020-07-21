<?php

class Db
{
    private $host = 'localhost';
    private $dbname = 'dbname';
    private $user = 'user';
    private $password = 'pas';

    private function setConnection()
    {
        try {
            $DBH = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
            $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return $DBH;
    }

    /**
     * @var string $path
     * @var string $text
     * @var bool $active
     **/
    private function logToFile($path, $text, $active = true)
    {
        if ($active) {
            file_put_contents($path, $text, FILE_APPEND);
        }

    }

    /**
     * @var array $arData
     * @return  array $res
     **/
    public function ManageSitesTable($arSite)
    {
        $res = [];
        if ($arSite) {
            $DBH = $this->setConnection();
            $date = date('Y-m-d H:i:s');
            $updatedCnt = 0;
            $addedCnt = 0;
            if (is_object($DBH)) {
                $arSites = [];
                try {
                    $sql = sprintf('SELECT id,created, domain from websites WHERE domain="%s"', $arSite["domain"]);
                    $STH = $DBH->query($sql);
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $STH->fetch()) {
                        $arSites[$row['domain']] = $row;
                    }

                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/websitesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                try {
                    $STHINSERT = $DBH->prepare("INSERT INTO websites ( domain,image_src,global_rank,country_rank,main_country,current_visits,pages_per_visit,bounce_rate,percent_direct,percent_referrals,percent_search,percent_social,percent_mail,percent_display,updated,created ) values ( :domain,:image_src,:global_rank,:country_rank,:main_country,:current_visits,:pages_per_visit,:bounce_rate:percent_direct,:percent_referrals,:percent_search,:percent_social,:percent_mail,:percent_display,:updated,:created ) ");
                    $STHIUPDATE = $DBH->prepare("UPDATE websites SET  domain=:domain,image_src=:image_src,global_rank=:global_rank,country_rank=:country_rank,main_country=:main_country,current_visits=:current_visits,pages_per_visit=:pages_per_visit,bounce_rate=:bounce_rate,percent_direct=:percent_direct,percent_referrals=:percent_referrals,percent_search=:percent_search,percent_social=:percent_social,percent_mail=:percent_mail,percent_display=:percent_display,updated=:updated,created=:created WHERE id=:id");
                    if (isset($arSites[$arSite['domain']]['id'])) {
                        $arFields = [
                            'id' => $arSites[$arSite['domain']]['id'],
                            'domain' => $arSite['domain'],
                            'image_src' => $arSite['image_src'],
                            'global_rank' => $arSite['global_rank'],
                            'country_rank' => $arSite['country_rank'],
                            'main_country' => $arSite['main_country'],
                            'current_visits' => $arSite['current_visits'],
                            'pages_per_visit' => $arSite['pages_per_visit'],
                            'bounce_rate' => $arSite['bounce_rate'],
                            'percent_direct' => isset($arSite['traffic']['Direct']) ? $arSite['traffic']['Direct'] : '',
                            'percent_referrals' => isset($arSite['traffic']['Referrals']) ? $arSite['traffic']['Referrals'] : '',
                            'percent_search' => isset($arSite['traffic']['Search']) ? $arSite['traffic']['Search'] : '',
                            'percent_social' => isset($arSite['traffic']['Social']) ? $arSite['traffic']['Social'] : '',
                            'percent_mail' => isset($arSite['traffic']['Mail']) ? $arSite['traffic']['Mail'] : '',
                            'percent_display' => isset($arSite['traffic']['Paid_Referrals']) ? $arSite['traffic']['Paid_Referrals'] : '',
                            'updated' => $date,
                            'created' => isset($arSites[$arSite['domain']]['created']) ? $arSites[$arSite['domain']]['created'] : $date
                        ];
                        $result = $STHIUPDATE->execute($arFields);
                        if ($result) {
                            $updatedCnt++;
                            $this->logToFile(sprintf('logs/websitesTableSuccessUpdate%s.txt', $date),
                                sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                        }
                    } else {
                        $arFields = [
                            'domain' => $arSite['domain'],
                            'image_src' => $arSite['image_src'],
                            'global_rank' => $arSite['global_rank'],
                            'country_rank' => $arSite['country_rank'],
                            'main_country' => $arSite['main_country'],
                            'current_visits' => $arSite['current_visits'],
                            'pages_per_visit' => $arSite['pages_per_visit'],
                            'bounce_rate' => $arSite['bounce_rate'],
                            'percent_direct' => isset($arSite['traffic']['Direct']) ? $arSite['traffic']['Direct'] : '',
                            'percent_referrals' => isset($arSite['traffic']['Referrals']) ? $arSite['traffic']['Referrals'] : '',
                            'percent_search' => isset($arSite['traffic']['Search']) ? $arSite['traffic']['Search'] : '',
                            'percent_social' => isset($arSite['traffic']['Social']) ? $arSite['traffic']['Social'] : '',
                            'percent_mail' => isset($arSite['traffic']['Mail']) ? $arSite['traffic']['Mail'] : '',
                            'percent_display' => isset($arSite['traffic']['Paid_Referrals']) ? $arSite['traffic']['Paid_Referrals'] : '',
                            'updated' => $date,
                            'created' => $date
                        ];
                        $result = $STHINSERT->execute($arFields);
                        if ($result) {
                            $addedCnt++;
                            $this->logToFile(sprintf('logs/websitesTableSuccessAdd%s.txt', $date),
                                sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                        }
                    }
                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/websitesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                $DBH = null;

            } else {
                $this->logToFile(sprintf('logs/PDOErrors%s.txt', $date), sprintf('%s%s', $DBH['MESSAGE'], PHP_EOL));
            }

        }
        return $res;
    }

    /**
     * @var array $arData
     * @return  array $res
     **/
    public function ManageCategoryNames($arCategory)
    {
        $res = [];
        if ($arCategory) {
            $DBH = $this->setConnection();
            $date = date('Y-m-d H:i:s');
            $updatedCnt = 0;
            $addedCnt = 0;
            if (is_object($DBH)) {
                $arCategories = [];
                try {
                    $sql = sprintf('SELECT id,name from categories_names WHERE name="%s"', $arCategory["name"]);
                    $STH = $DBH->query($sql);
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $STH->fetch()) {
                        $arCategories[$row['name']] = $row['id'];
                    }

                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/categories_namesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                try {
                    $STHINSERT = $DBH->prepare("INSERT INTO categories_names ( name ) values ( :name ) ");
                    $STHIUPDATE = $DBH->prepare("UPDATE categories_names SET  name=:name WHERE id=:id");
                    if ($arCategory['name']) {
                        if (isset($arCategories[$arCategory['name']])) {
                            $arFields = [
                                'id' => $arCategories[$arCategory['name']],
                                'name' => $arCategory['name'],
                            ];
                            $result = $STHIUPDATE->execute($arFields);
                            if ($result) {
                                $updatedCnt++;
                                $this->logToFile(sprintf('logs/categories_namesTableSuccessUpdate%s.txt', $date),
                                    sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                            }
                        } else {
                            $arFields = [
                                'name' => $arCategory['name'],
                            ];
                            $result = $STHINSERT->execute($arFields);
                            if ($result) {
                                $addedCnt++;
                                $this->logToFile(sprintf('logs/categories_namesTableSuccessAdd%s.txt', $date),
                                    sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                            }
                        }

                    }

                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/categories_namesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                $DBH = null;

            } else {
                $this->logToFile(sprintf('logs/PDOErrors%s.txt', $date), sprintf('%s%s', $DBH['MESSAGE'], PHP_EOL));
            }

        }
        return $res;
    }

    /**
     * @var array $arData
     * @return  array $res
     **/
    public function ManageCategories($arCategory)
    {
        $res = [];
        if ($arCategory) {
            $DBH = $this->setConnection();
            $date = date('Y-m-d H:i:s');
            $updatedCnt = 0;
            $addedCnt = 0;
            if (is_object($DBH)) {
                $arCategories = [];
                $arCategories_name = [];
                $arSites = [];
                try {
                    $STH = $DBH->query('SELECT id,categories_names_id,website_id from categories');
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $STH->fetch()) {
                        $arCategories[$row['website_id']] = $row;
                    }
                    $sql = sprintf('SELECT id,name from categories_names WHERE name="%s"',
                        $arCategory["category_name"]);
                    $STH = $DBH->query($sql);
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $STH->fetch()) {
                        $arCategories_name[$row['name']] = $row['id'];
                    }
                    $sql = sprintf('SELECT id, domain from websites WHERE domain="%s"', $arCategory["domain"]);
                    $STH = $DBH->query($sql);
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $STH->fetch()) {
                        $arSites[$row['domain']] = $row['id'];
                    }


                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/categoriesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                try {
                    $STHINSERT = $DBH->prepare("INSERT INTO categories ( categories_names_id,website_id ) values ( :categories_names_id, :website_id ) ");
                    $STHIUPDATE = $DBH->prepare("UPDATE categories SET  categories_names_id=:categories_names_id WHERE id=:id");
                    if ($arCategory['domain'] && $arCategory['category_name']) {
                        $website_id = isset($arSites[$arCategory['domain']]) ? $arSites[$arCategory['domain']] : '';
                        $category_id = isset($arCategories_name[$arCategory['category_name']]) ? $arCategories_name[$arCategory['category_name']] : '';
                        if ($website_id && $category_id) {
                            if (isset($arCategories[$website_id]['id'])) {
                                $arFields = [
                                    'id' => $arCategories[$website_id]['id'],
                                    'categories_names_id' => $category_id,
                                ];
                                $result = $STHIUPDATE->execute($arFields);
                                if ($result) {
                                    $updatedCnt++;
                                    $this->logToFile(sprintf('logs/categoriesTableSuccessUpdate%s.txt', $date),
                                        sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                                }
                            } else {
                                $arFields = [
                                    'categories_names_id' => $category_id,
                                    'website_id' => $website_id,
                                ];
                                $result = $STHINSERT->execute($arFields);
                                if ($result) {
                                    $addedCnt++;
                                    $this->logToFile(sprintf('logs/categoriesTableSuccessAdd%s.txt', $date),
                                        sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                                }
                            }
                        }
                    }


                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/categoriesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                $DBH = null;

            } else {
                $this->logToFile(sprintf('logs/PDOErrors%s.txt', $date), sprintf('%s%s', $DBH['MESSAGE'], PHP_EOL));
            }

        }
        return $res;
    }

    /**
     * @var array $arData
     * @return  array $res
     **/
    public function ManageDynamics($arCategory)
    {
        $res = [];
        if ($arCategory) {
            $DBH = $this->setConnection();
            $date = date('Y-m-d H:i:s');
            $updatedCnt = 0;
            $addedCnt = 0;
            if (is_object($DBH)) {
                $arDates = [];
                $arSites = [];
                try {
                    $STH = $DBH->query('SELECT id,websites_id,date,traffic_volume from dates');
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $STH->fetch()) {
                        $arDates[$row['websites_id']][$row['date']] = $row;
                    }
                    $STH = $DBH->query('SELECT id, domain from websites');
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $STH->fetch()) {
                        $arSites[$row['domain']] = $row['id'];
                    }

                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/datesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                try {
                    $STHINSERT = $DBH->prepare("INSERT INTO dates ( date,traffic_volume,websites_id ) values ( :date, :traffic_volume,:websites_id ) ");
                    $STHIUPDATE = $DBH->prepare("UPDATE dates SET  traffic_volume=:traffic_volume WHERE id=:id");

                    foreach ($arCategory as $arDate) {
                        if (!$arDate['domain'] || !$arDate['date'] || !$arDate['traffic_volume']) {
                            continue;
                        }
                        $website_id = isset($arSites[$arDate['domain']]) ? $arSites[$arDate['domain']] : '';
                        if (!$website_id) {
                            continue;
                        }
                        if (isset($arDates[$website_id][$arDate['date']]['id'])) {
                            $arFields = [
                                'id' => $arDates[$website_id][$arDate['date']]['id'],
                                'traffic_volume' => $arDate['traffic_volume'],
                            ];
                            $result = $STHIUPDATE->execute($arFields);
                            if ($result) {
                                $updatedCnt++;
                                $this->logToFile(sprintf('logs/datesTableSuccessUpdate%s.txt', $date),
                                    sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                            }
                        } else {
                            $arFields = [
                                'websites_id' => $website_id,
                                'date' => $arDate['date'],
                                'traffic_volume' => $arDate['traffic_volume'],
                            ];
                            $result = $STHINSERT->execute($arFields);
                            if ($result) {
                                $addedCnt++;
                                $this->logToFile(sprintf('logs/datesTableSuccessAdd%s.txt', $date),
                                    sprintf('%s;%s', json_encode($arFields), PHP_EOL));
                            }
                        }
                    }

                } catch (PDOException $e) {
                    $this->logToFile(sprintf('logs/datesTableErrors%s.txt', $date),
                        sprintf('%s%s', $e->getMessage(), PHP_EOL));
                    $res[] = $e->getMessage();
                }
                $DBH = null;

            } else {
                $this->logToFile(sprintf('logs/PDOErrors%s.txt', $date), sprintf('%s%s', $DBH['MESSAGE'], PHP_EOL));

            }

        }
        return $res;
    }

}