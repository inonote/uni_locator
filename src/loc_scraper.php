<?php
  /*
    uni_locator
    Copyright (C) 2020 inonote

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
  */
  
  namespace LocScraper {
    Class Location {
      public $store_name = ''; // 店舗名
      public $store_address = ''; // 住所
      public $store_pos = ''; // 位置
      public $hash = ''; // ariacode,prefcode,store_name でmd5
    }

    Class Scraper {
      private const ENDPOINT = 'https://location.am-all.net/alm/location';
      
      public const ARIACODE_JAPAN = 1000;         // 日本 Japan
      public const ARIACODE_TAIWAN = 1001;        // 台湾 Taiwan
      public const ARIACODE_HONGKONG = 1002;      // 香港 Hong Kong
      public const ARIACODE_SINGAPORE = 1003;     // シンガポール Singapore
      public const ARIACODE_MALAYSIA = 1004;      // マレーシア Malaysia
      public const ARIACODE_KOREA = 1005;         // 韓国 Korea
      public const ARIACODE_THAILAND = 1006;      // タイ Thailand
      public const ARIACODE_INDONESIA = 1007;     // インドネシア Indonesia
      public const ARIACODE_MACAU = 1008;         // マカオ Macau
      public const ARIACODE_AMERICA = 1009;       // 米国 America
      public const ARIACODE_PHILIPPINES = 1010;   // フィリピン Philippines
      public const ARIACODE_VIETNAM = 1011;       // ベトナム Vietnam
      public const ARIACODE_AUSTRALIA = 1012;     // オーストラリア Australia
      public const ARIACODE_MYANMAR = 1013;       // ミャンマー Myanmar
      public const ARIACODE_NEWZEALAND = 1014;    // ニュージーランド New Zealand

      private $game_id = 0;

      public function set_game_id(int $game_id) : void {
        $this->game_id = $game_id;
      }

      public function scan(int $ariacode, int $prefcode) : array {
        $stores = [];
        $url = Scraper::ENDPOINT.'?gm='.$this->game_id.'&ct='.$ariacode.'&at='.$prefcode;
        $html = Scraper::get($url);
        if ($html !== null) {
          // ゴリゴリ正規表現
          if (preg_match_all('/<span class="store_name">[\s\S]*?<\/button>/', $html, $blocks, PREG_SET_ORDER) > 0) {
            foreach ($blocks as $block) {
              $location = new Location;
              if (preg_match('/<span class="store_name">(.*?)<\\/span>/', $block[0], $mtch) === 1)
                $location->store_name = $mtch[1];
              if (preg_match('/<span class="store_address">(.*?)<\\/span>/', $block[0], $mtch) === 1)
                $location->store_address = $mtch[1];
              if (preg_match('/\\/\\/maps\\.google\\.com\\/maps\\?q=.*?@([0-9.,]*?)&zoom/', $block[0], $mtch) === 1)
                $location->store_pos = $mtch[1];
              $location->hash = md5($ariacode.','.$prefcode.','.$location->store_name);
              array_push($stores, $location);
            }
          }
        }
        return $stores;
      }

      private static function get(string $url) : ?string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.3']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        $b = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        if (CURLE_OK !== $errno) {
          return null;
        }
        return $b;
      }
    }
  }

  