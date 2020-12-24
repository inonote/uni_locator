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

(function(doc, win) {
  let locations = [];
  let locations_diff = [];
  let load_success = false;
  let elm_sb_result = doc.getElementById("sb_result");
  let elm_sb_pref = doc.getElementById("sb_pref");
  let elm_sb_store_name = doc.getElementById("sb_store_name");
  let elm_sb_result_count = doc.getElementById("sb_result_count");
  let elm_sb_search_near_store = doc.getElementById("sb_search_near_store");
  let elm_note_search_near_store = doc.getElementById("note_search_near_store");

  function load_locations_list(callback) {
    load_json("/data/locations.json", function(resp) {
      locations = resp;
      load_json("/data/locations_diff.json", function(resp) {
        locations_diff = resp;
        load_success = true;
        if (callback)
          callback();
      }, undefined);
    },
    undefined);
  }

  function load_json(url, callback_success, callback_error) {
    let xhr = new XMLHttpRequest;
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        if( xhr.status == 200 || xhr.status == 304 ) {
          if (callback_success)
            callback_success(xhr.response);
        }
        else {
          if (callback_error)
            callback_error(xhr.status);
        }
      }
    }
    xhr.responseType = "json";
    xhr.open("GET", url);
    xhr.send(null);
  }

  function draw_list() {
    if (!load_success)
      return;
    
    let result = [];
    let pref = parseInt(elm_sb_pref.value);
    let keywords = normalize(String(elm_sb_store_name.value).trim()).split(/\s/);
    if (keywords.length === 1 && keywords[0] === "")
      keywords = [];
    let keywords_count = keywords.length;
    let geolocation = null;
    for(let keyword_index = 0; keyword_index < keywords_count; keyword_index++) {
      let keyword = keywords[keyword_index];
      if (keyword.substr(0, 1) === "@") { // @から始まってたら地理座標を表す
        if (geolocation === null) {
          geolocation = keyword.substr(1).split(",");
          if (typeof geolocation !== "object" || geolocation.length !== 2)
            geolocation = null;
          else
            for(let i = 0; i < geolocation.length; i++)
              geolocation[i] = parseFloat(geolocation[i]);
        }
        keywords[keyword_index] = "";
      }
    }

    if (pref !== 9999 || keywords_count > 0) {

      // 店舗を検索
      for(let prefcode = 0; prefcode < locations.length; prefcode++) {
        if (pref !== 9999 && prefcode !== pref)
          continue;
        let v = locations[prefcode];
        for(let i = 0; i < v.stores.length; i++) {
          let store = v.stores[i];
          let distance = null;
          if (keywords_count > 0) {
            let found = true;
            let hiraganaized_store_name = normalize(store.store_name);
            // 店舗名で検索
            for(let keyword_index = 0; keyword_index < keywords_count; keyword_index++) {
              if (hiraganaized_store_name.indexOf(keywords[keyword_index]) === -1) {
                found = false;
                break;
              }
            }
            if (!found) { // 店舗名で見つからなかったら住所で検索
              found = true;
              let hiraganaized_store_address = normalize(store.store_address);
              for(let keyword_index = 0; keyword_index < keywords_count; keyword_index++) {
                if (hiraganaized_store_address.indexOf(keywords[keyword_index]) === -1) {
                  found = false;
                  break;
                }
              }
              if (!found)
                continue;
            }
          }
          if (geolocation !== null) {
            distance = geo_distance(geolocation, store.store_pos.split(","));
            if (distance > 120000.0) // 120km まで
              continue;
          }
          result.push({
            pref: v.name,
            store: store,
            distance: distance
          });
        }
      }
      elm_sb_result_count.parentElement.classList.remove("d-none");
      elm_note_search_near_store.classList.add("d-none");
    }
    else {
      elm_sb_result_count.parentElement.classList.add("d-none");
      elm_note_search_near_store.classList.remove("d-none");
    }

    // 近隣店舗検索時は近い順に並べ替える
    if (geolocation !== null) {
      result.sort(function(a, b) {
        return a.distance - b.distance;
      });
    }

    let elm_list = doc.createElement("div");
    elm_list.setAttribute("class", "list-group list-group-flush");
    if (elm_sb_result.firstChild)
      elm_sb_result.removeChild(elm_sb_result.firstChild);

    let last_pref = "";
    for(let i = 0; i < result.length; i++) {
      let elm_list_item = doc.createElement("div");
      if (last_pref !== result[i].pref) {
        last_pref = result[i].pref
        elm_list_item.setAttribute("class", "pl-2 mt-3 h6 font-weight-bold");
        elm_list_item.innerText = result[i].pref;
        elm_list.appendChild(elm_list_item);

        elm_list_item = doc.createElement("div");
      }

      elm_list_item.setAttribute("class", "list-group-item pl-4");
      
      let elm_list_item_p1 = doc.createElement("p");
      elm_list_item_p1.setAttribute("class", "mb-0 font-weight-bold");
      elm_list_item_p1.innerText = result[i].store.store_name;
      elm_list_item.appendChild(elm_list_item_p1);

      let elm_list_item_p2 = doc.createElement("p");
      elm_list_item_p2.setAttribute("class", "text-muted mb-1");
      elm_list_item_p2.innerText = result[i].store.store_address;
      elm_list_item.appendChild(elm_list_item_p2);

      if (geolocation !== null) {
        let elm_list_item_p3 = doc.createElement("p");
        elm_list_item_p3.setAttribute("class", "mb-1");
        elm_list_item_p3.innerText = "約 " + (result[i].distance / 1000).toFixed(2) + " km";
        elm_list_item.appendChild(elm_list_item_p3);
      }

      let elm_list_item_gmap = doc.createElement("button");
      elm_list_item_gmap.setAttribute("class", "btn btn-link float-right");
      elm_list_item_gmap.innerText = "Google Maps で見る ≫";
      elm_list_item_gmap.setAttribute("data-pos", result[i].store.store_name + "@" + result[i].store.store_pos);
      elm_list_item_gmap.addEventListener("click", store_list_gmap_button_onclick);
      elm_list_item.appendChild(elm_list_item_gmap);
      elm_list.appendChild(elm_list_item);
    }
    elm_sb_result.appendChild(elm_list);
    elm_sb_result_count.innerText = result.length;
  }

  function normalize(input) {
    return input.toUpperCase()
                .normalize("NFKD")
                .replace(/[\u3099\u309A]/gu, "")
                .replace(/[ぁ-ん]/g, function(s) {
                  return String.fromCharCode(s.charCodeAt(0) + 0x60);
                }).replace(/[ァィゥェォッャュョヮ]/g, function(s) {
                  return {"ァ": "ア",
                          "ィ": "イ",
                          "ゥ": "ウ",
                          "ェ": "エ",
                          "ォ": "オ",
                          "ッ": "ツ",
                          "ャ": "ヤ",
                          "ュ": "ユ",
                          "ョ":"ヨ",
                          "ヮ": "ワ"}[s];
                }).replace(/ナムコ|NAMUCO|NAMKO|NAMUKO/g, "NAMCO")
                .replace(/SEGA/g, "セカ");
  }

  function store_list_gmap_button_onclick(e) {
    let pos = e.target.getAttribute("data-pos");
    win.open("https://www.google.com/maps?q="+encodeURI(pos)+"&zoom=16", "_blank");
  }

  function set_current_location() {
    navigator.geolocation.getCurrentPosition(function(pos) {
      // 現在地の座標を検索窓にセット
      elm_sb_pref.value = 9999;
      elm_sb_store_name.value = "@"+pos.coords.latitude+","+pos.coords.longitude;
      draw_list();
    }, function() {
      win.alert("エラー: 現在地の取得に失敗しました。");
    });
  }
  
  function geo_distance(a, b) {
    // GRS 80
    const radius_x = 6378137.0;
    const e2 = 0.00669438002301199714049729123684;
    const distance_y = deg2rad(b[0]) - deg2rad(a[0]);
    const distance_x = deg2rad(b[1]) - deg2rad(a[1]);
    const avg_y = (deg2rad(b[0]) + deg2rad(a[0])) / 2;
    const w = Math.sqrt(1 - e2 * Math.sin(avg_y) * Math.sin(avg_y));
    const p1 = distance_y * radius_x * (1 - e2) / (w * w * w);
    const p2 = distance_x * radius_x / w * Math.cos(avg_y);
    return Math.sqrt(p1 * p1 + p2 * p2);
  }
  
  function deg2rad(deg) {
    return deg * Math.PI / 180.0;
  }

  load_locations_list(function() {
    elm_sb_pref.addEventListener("input", draw_list);
    (function() {
      let input_timer_handle = null;
      function input_timer() {
        if (input_timer_handle)
          win.clearTimeout(input_timer_handle);
        input_timer_handle = win.setTimeout(draw_list, 250);
      }
      elm_sb_search_near_store.addEventListener("click", set_current_location);
      elm_sb_store_name.addEventListener("input", input_timer);
    })();
    draw_list();
  });
})(document, window);
