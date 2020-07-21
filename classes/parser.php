<?php

class Parser
{
    /**
     * @var string $html
     * @return array $result
     **/
    public function ParseDynamicToDates($html)
    {
        $result = [];
        $arMatches = [];
        $siteDomain = '';
        if (preg_match('/Sw.siteDomain = "(.*?)";/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $siteDomain = $arMatches[1];
            }
        }
        if (preg_match('/"WeeklyTrafficNumbers":{(.*?)}/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $arMatches = explode(',', $arMatches[1]);
                foreach ($arMatches as $arMatch) {
                    $date = explode(':', str_replace('"', '', $arMatch));
                    $volume = explode(':', $arMatch);

                    $result[] = [
                        'domain' => $siteDomain,
                        'date' => isset($date[0])?$date[0]:'',
                        'traffic_volume' => isset($volume[1])?$volume[1]:'',
                    ];
                }
            }
        }
        return $result;

    }

    /**
     * @var string $html
     * @return array $result
     **/
    public function ParseSite($html)
    {
        $arMatches = [];
        $siteDomain = '';
        $siteCountry = '';
        $image_src = '';
        $global_rank = '';
        $country_rank = '';
        $siteVisits = '';
        $sitePages = '';
        $siteBounce = '';
        $arTraffic = [];
        if (preg_match('/Sw.siteDomain = "(.*?)";/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $siteDomain = $arMatches[1];
            }
        }
        if (preg_match('/Sw.siteCountry = (.*?);/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                preg_match('/data-analytics-label="Country Rank(.*?)>(.*?)<\/a>/', $html, $arMatches);

                $siteCountry = isset($arMatches[2])? $arMatches[2]: '';
            }
        }

        if(!$siteCountry){

            if (preg_match('/data-geochart=(.*?)}/', $html, $arMatches)) {

                if (isset($arMatches[1])) {
                    preg_match('/,\[&quot;(.*?)&quot;,(.*?)\]/', $html, $arMatches);
                    $siteCountry = isset($arMatches[1])? $arMatches[1]: '';
                }
            }
        }
        if (preg_match('/"GlobalRank":\[(.*?),/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $global_rank = $arMatches[1];
            }
        }
        if (preg_match('/"CountryRanks":{(.*?)\[(.*?),/', $html, $arMatches)) {
            if ($arMatches[2]) {
                $country_rank = $arMatches[2];
            }
        }
        if (preg_match('/"TrafficSources":{(.*?)}/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $arMatches = explode(',', $arMatches[1]);
                foreach ($arMatches as $arMatch) {
                    $exp = explode(':', $arMatch);
                    $arTraffic[str_replace(['"', ' '], ['', '_'], $exp[0])] = $exp[1];
                }
            }
        }
        if (preg_match('/"TotalLastMonthVisits":(.*?),/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $siteVisits = $arMatches[1];
            }
        }
        if (preg_match('/"BounceRate":"(.*?)%",/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $siteBounce = $arMatches[1];
            }
        }
        if (preg_match('/"PageViews":(.*?),/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $sitePages = $arMatches[1];
            }
        }
        if (preg_match('/<img itemprop="image" class="websiteHeader-screenImg" src="(.*?)"/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $image_src = $arMatches[1];
            }
        }

        $result = [
            'domain' => $siteDomain,
            'image_src' => $image_src,
            'global_rank' => $global_rank,
            'country_rank' => $country_rank,
            'main_country' => $siteCountry,
            'current_visits' => $siteVisits,
            'pages_per_visit' => $sitePages,
            'bounce_rate' => $siteBounce,
            'traffic' => $arTraffic
        ];
        return $result;

    }

    /**
     * @var string $html
     * @return array $result
     **/
    public function ParseCategoriesNames($html)
    {
        $arMatches = [];
        $categoryName = '';
        if (preg_match('/"Category":"(.*?)"/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $categoryName = explode('/', $arMatches[1])[0];
            }
        }
        $result = [
            'name' => $categoryName,
        ];
        return $result;

    }

    /**
     * @var string $html
     * @return array $result
     **/
    public function ParseCategories($html)
    {
        $arMatches = [];
        $siteDomain = '';
        $categoryName = '';
        if (preg_match('/Sw.siteDomain = "(.*?)";/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $siteDomain = $arMatches[1];
            }
        }
        if (preg_match('/"Category":"(.*?)"/', $html, $arMatches)) {
            if (isset($arMatches[1])) {
                $categoryName = explode('/', $arMatches[1])[0];
            }
        }
        $result = [
            'domain' => $siteDomain,
            'category_name' => $categoryName,
        ];
        return $result;

    }

}