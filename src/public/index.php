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
?>
<!doctype html>
<html lang="ja" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>ウニ設置店舗検索</title>
  </head>
  <body class="d-flex flex-column h-100">
    <main role="main" class="flex-shrink-0">
      <div class="container">
        <div class="text-center mt-3 mb-5">
          <h1 class="h2">CHUNITHM 設置店舗検索</h1>
        </div>
        <div style="max-width:600px;margin:auto;">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <select class="custom-select" style="border-top-right-radius: 0;border-bottom-right-radius:0;" id="sb_pref">
                <option value="9999" selected>全都道府県</option>
                <?php
                  foreach($pref_list as $prefcode => $pref_name) {
                    echo '<option value="'.$prefcode.'">'.htmlspecialchars($pref_name).'</option>';
                  }
                ?>
              </select>
            </div>
            <input type="search" class="form-control" placeholder="店舗名もしくは住所 (部分一致)" id="sb_store_name">
          </div>
          <p class="mb-1 d-none"><strong id="sb_result_count"></strong> 店舗見つかりました。</p>
          <div class="mb-3" id="sb_result">
          </div>
          <div class="mb-4 p-3 border rounded shadow-sm" id="note_search_near_store">
            <div class="d-flex justify-content-center mb-3"><button type="button" class="btn btn-success" id="sb_search_near_store">現在地に近い店舗を探す</button></div>
            <p class="mb-1">端末の GPS 機能を使用して探します。現在地に関する情報は外部に送信されません。</p>
            <p class="mb-1">端末に GPS 機能が無い、または無効化している場合はご利用いただけません。</p>
          </div>
          <div class="mb-4 p-3 border rounded shadow-sm">
            <h5 class="font-weight-bold">このサイトについて</h5>
            <p class="mb-1">ここに掲載されている情報は、毎朝 8 時頃に取得する ALL.Netサービス対応店舗検索の内容をもとにしています。</p>
            <p class="mb-1">オフライン状態になっている等の原因で店舗が一覧に掲載されない場合がございます。</p>
          </div>
        </div>
      </div>
    </main>
    <footer class="footer mt-auto py-2 small bg-light">
      <div class="container">
        <span class="text-muted mr-2">(c) 2020 inonote</span>
        <a href="https://github.com/inonote/uni_locator" target="_blank">Fork me on GitHub</a>
        <p>この Web アプリケーションは、<a href="https://github.com/inonote/uni_locator/blob/master/LICENSE" target="_blank">AGPL v3.0</a> にもとづきライセンスされています。</p>
      </div>
    </footer>
    <script src="main.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>