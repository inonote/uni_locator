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
  
  include 'config.php';
  include 'loc_scraper.php';
  use LocScraper\Scraper;

  $scraper = new Scraper;
  $scraper->set_game_id($config['game_id']);

  $pref_list = [
    0  => '北海道',
    1  => '青森県',
    2  => '岩手県',
    3  => '宮城県',
    4  => '秋田県',
    5  => '山形県',
    6  => '福島県',
    7  => '茨城県',
    8  => '栃木県',
    9  => '群馬県',
    10 => '埼玉県',
    11 => '千葉県',
    12 => '東京都',
    13 => '神奈川県',
    14 => '新潟県',
    15 => '富山県',
    16 => '石川県',
    17 => '福井県',
    18 => '山梨県',
    19 => '長野県',
    20 => '岐阜県',
    21 => '静岡県',
    22 => '愛知県',
    23 => '三重県',
    24 => '滋賀県',
    25 => '京都府',
    26 => '大阪府',
    27 => '兵庫県',
    28 => '奈良県',
    29 => '和歌山県',
    30 => '鳥取県',
    31 => '島根県',
    32 => '岡山県',
    33 => '広島県',
    34 => '山口県',
    35 => '徳島県',
    36 => '香川県',
    37 => '愛媛県',
    38 => '高知県',
    39 => '福岡県',
    40 => '佐賀県',
    41 => '長崎県',
    42 => '熊本県',
    43 => '大分県',
    44 => '宮崎県',
    45 => '鹿児島県',
    46 => '沖縄県'
  ];

  $now = time();
  $last_locations = json_decode(@file_get_contents($config['working_dir'].'/locations.json'), true);
  $locations_diff = json_decode(@file_get_contents($config['working_dir'].'/locations_diff.json'), true);
  if ($last_locations === null)
    $last_locations = [];
  if ($locations_diff === null)
    $locations_diff = [];
  $locations = [];

  foreach($pref_list as $prefcode => $pref_name) {
    echo 'Scraping... '. $pref_name, PHP_EOL;
    $locations[$prefcode] = [
      'name' => $pref_name,
      'stores' => $scraper->scan(Scraper::ARIACODE_JAPAN, $prefcode)
    ];
    sleep(1);
  }

  if (is_array($last_locations)) {
    // 差分 (開店)
    foreach($locations as $index => $v) {
      if (!isset($locations_diff[$index])) {
        $locations_diff[$index] = [
          'name' => $v['name'],
          'stores' => [],
        ];
      }

      foreach($v['stores'] as $store) {
        $found = false;
        foreach($last_locations[$index]['stores'] as $store2) {
          if ($store->hash === $store2['hash']) {
            $found = true;
            break;
          }
        }
        if (!$found) {
          array_push($locations_diff[$index]['stores'], [
            'op' => 'add',
            'store' => $store,
            'time' => $now
          ]);
        }
      }
    }

    // 差分 (閉店)
    foreach($last_locations as $index => $v) {
      if (!isset($locations_diff[$index])) {
        $locations_diff[$index] = [
          'name' => $v['name'],
          'stores' => [],
        ];
      }

      foreach($v['stores'] as $store) {
        $found = false;
        foreach($locations[$index]['stores'] as $store2) {
          if ($store['hash'] === $store2->hash) {
            $found = true;
            break;
          }
        }
        if (!$found) {
          array_push($locations_diff[$index]['stores'], [
            'op' => 'del',
            'store' => $store,
            'time' => $now
          ]);
        }
      }
    }

    // 30日より前の閉店開店情報は削除
    foreach($locations_diff as $index => $v) {
      foreach($v['stores'] as $index2 => $v2) {
        if ($v2['time'] + 2592000 /*(30 * 24 * 60 * 60)*/ < $now) {
          unset($locations_diff[$index]['stores'][$index2]);
        }
      }
    }
  }

  file_put_contents($config['working_dir'].'/locations_diff.json', json_encode($locations_diff, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
  file_put_contents($config['working_dir'].'/locations.json', json_encode($locations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
