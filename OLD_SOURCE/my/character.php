<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
$user = UserUtils::RetrieveUser();

if ($user == null) {
    die(header('Location: /login'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    $page_title = 'Your Character - Zomium';
    $page_styles = ['/css/new/my/character.css?v=2'];
    $page_scripts = ['/js/character.js?t=1771413909'];
    include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
    ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body.character-screen {
            background: #1e1e22;
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .character-page { padding: 24px 0 48px; }
        .container { max-width: 1260px; margin: 0 auto; padding: 0 20px; }

        .character-shell { display: flex; gap: 18px; align-items: flex-start; }

        /* ── Sidebar ── */
        .character-sidebar { width: 210px; flex-shrink: 0; display: flex; flex-direction: column; gap: 14px; }
        .c-panel { background: #2b2b31; border-radius: 10px; overflow: hidden; }
        .c-summary { display: flex; flex-direction: column; }
        .c-panel-head { padding: 13px 16px 11px; border-bottom: 1px solid #35353d; text-align: center; }
        .c-panel-head h1 { font-size: 15px; font-weight: 600; color: #fff; }

        .btn-regen {
            margin: 12px 14px 0;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            background: #1d6ff2; color: #fff; border: none; border-radius: 6px;
            padding: 9px 0; font-size: 13px; font-weight: 600; cursor: pointer;
            transition: background 0.15s; width: calc(100% - 28px);
        }
        .btn-regen:hover { background: #1558cc; }

        .c-render-stage { padding: 14px 16px; display: flex; justify-content: center; }
        .c-render-stage img { width: 140px; height: 140px; object-fit: contain; }

        .c-wearing { padding: 13px 14px 14px; }
        .c-wearing-head { margin-bottom: 10px; }
        .c-wearing-head h2 { font-size: 13px; font-weight: 600; color: #fff; }
        .c-wearing-head p  { font-size: 11px; color: #666; margin-top: 2px; }

        #CurrentlyWearing #AssetsContainer > table { width: 100%; border-collapse: collapse; }
        #CurrentlyWearing #AssetsContainer > table td { padding: 3px; width: 50%; }

        /* ── Workspace ── */
        .c-workspace { flex: 1; background: #2b2b31; border-radius: 10px; overflow: hidden; min-width: 0; }

        .c-tabs { display: flex; border-bottom: 1px solid #35353d; padding: 0 6px; }
        .c-tab {
            display: inline-block; padding: 13px 20px; font-size: 13px; font-weight: 500;
            color: #4ab3f4; text-decoration: none; border-bottom: 2px solid transparent;
            margin-bottom: -1px; cursor: pointer; transition: color 0.15s, border-color 0.15s;
            white-space: nowrap; user-select: none;
        }
        .c-tab:hover { color: #80cbff; }
        .c-tab.is-active { color: #fff; border-bottom-color: #fff; }

        .c-content { padding: 26px 28px 32px; }

        /* ── Body editor ── */
        .c-stage-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; color: #555; margin-bottom: 4px; }
        .c-stage-title { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 3px; }
        .c-stage-desc  { font-size: 12px; color: #777; margin-bottom: 22px; }

        #BodyColoursContainer {
            display: grid;
            grid-template-columns: 72px 116px 72px;
            grid-template-rows: 78px 118px 108px;
            gap: 5px;
            width: fit-content;
            margin: 0 auto 28px;
        }
        #BodyColoursContainer button[data_bodytype="0"] { grid-column: 2; grid-row: 1; }
        #BodyColoursContainer button[data_bodytype="2"] { grid-column: 1; grid-row: 2; }
        #BodyColoursContainer button[data_bodytype="1"] { grid-column: 2; grid-row: 2; }
        #BodyColoursContainer button[data_bodytype="3"] { grid-column: 3; grid-row: 2; }
        #BodyColoursContainer button[data_bodytype="4"] { grid-column: 2; grid-row: 3; justify-self: start; width: 54px; }
        #BodyColoursContainer button[data_bodytype="5"] { grid-column: 3; grid-row: 3; justify-self: start; width: 54px; }

        #BodyColoursContainer button {
            border: 2px solid transparent; border-radius: 7px; cursor: pointer;
            width: 100%; height: 100%;
            transition: transform 0.12s, border-color 0.12s, box-shadow 0.12s;
            outline: none; background: #555;
        }
        #BodyColoursContainer button:hover { transform: scale(1.06); border-color: rgba(255,255,255,0.35); }
        #BodyColoursContainer button.part-selected {
            border-color: #fff;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.2);
            transform: scale(1.04);
        }

        /* ── Inline Palette ── */
        .c-palette-box { background: #232327; border-radius: 9px; padding: 16px 18px; }
        .c-palette-title { font-size: 13px; font-weight: 600; color: #ccc; margin-bottom: 4px; }
        .c-palette-desc  { font-size: 11px; color: #666; margin-bottom: 13px; }

        #BodyColourPalette { display: flex; flex-wrap: wrap; gap: 6px; }
        .cswatch {
            width: 28px; height: 28px; border-radius: 5px; cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.1s, border-color 0.12s;
            flex-shrink: 0;
        }
        .cswatch:hover { transform: scale(1.2); }
        .cswatch.swatch-active { border-color: #fff; box-shadow: 0 0 0 2px rgba(255,255,255,0.25); }

        /* ── Wardrobe ── */
        .c-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .c-toolbar-title { font-size: 20px; font-weight: 700; color: #fff; }
        .c-search { display: flex; gap: 8px; }
        .c-search input {
            background: #1e1e22; border: 1px solid #3a3a42; border-radius: 6px;
            color: #e0e0e0; padding: 7px 12px; font-size: 13px; outline: none; width: 210px;
        }
        .c-search input:focus { border-color: #1d6ff2; }
        .c-search input::placeholder { color: #555; }
        .c-search .btn-search {
            background: #1d6ff2; color: #fff; border: none; border-radius: 6px;
            padding: 7px 16px; font-size: 13px; font-weight: 600; cursor: pointer;
            transition: background 0.15s;
        }
        .c-search .btn-search:hover { background: #1558cc; }

        #Wardrobe #AssetsContainer > table { width: 100%; border-collapse: separate; border-spacing: 10px; }
        #Wardrobe #AssetsContainer > table td { vertical-align: top; }

        .Asset { background: #232327; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; font-size: 12px; }
        .Asset #WearButton { width: 100%; padding: 7px; background: #1d6ff2; color: #fff; border: none; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
        .Asset #WearButton:hover { background: #1558cc; }
        .Asset #NameAndThumbs { display: block; padding: 10px; text-align: center; text-decoration: none; color: #ccc; }
        .Asset #NameAndThumbs img { width: 80px; height: 80px; object-fit: contain; display: block; margin: 0 auto 6px; }
        .Asset #NameAndThumbs span { font-size: 12px; display: block; }
        .Asset #Creator { display: block; text-align: center; font-size: 11px; color: #4ab3f4; text-decoration: none; padding: 0 10px 10px; }

        #Wardrobe #AssetsContainer #StatusText,
        #CurrentlyWearing #AssetsContainer #StatusText { font-size: 13px; color: #666; padding: 12px 0; }

        .c-paginator { display: flex; align-items: center; gap: 10px; justify-content: center; margin-top: 18px; }
        .c-page-btn { background: #1d6ff2; color: #fff; border-radius: 6px; padding: 7px 16px; font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.15s; }
        .c-page-btn:hover { background: #1558cc; }
        .c-paginator input { background: #1e1e22; border: 1px solid #3a3a42; border-radius: 6px; color: #e0e0e0; padding: 5px 8px; font-size: 13px; width: 52px; text-align: center; outline: none; }
        .c-paginator span { font-size: 13px; color: #888; }
    </style>
</head>
<body class="character-screen">

    <!-- Asset template — old IDs preserved for character.js -->
    <div class="Asset" template hidden>
        <button type="button" id="WearButton">Wear</button>
        <a id="NameAndThumbs" href="javascript:void(0)">
            <img src="" alt="">
            <span>Asset Name</span>
        </a>
        <a id="Creator" href="javascript:void(0)">
            <span>Creator</span>
        </a>
    </div>

    <!-- Dummy modal divs so character.js modal calls don't throw errors -->
    <div id="Colours" style="display:none;"></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>

    <main class="app-main character-page">
        <div class="container">
            <section class="character-shell">

                <!-- ═══ SIDEBAR ═══ -->
                <aside class="character-sidebar">
                    <div class="c-panel c-summary">
                        <div class="c-panel-head"><h1>Character</h1></div>
                        <button type="button" class="btn-regen" onclick="ANORRL.Character.RenderPlayer(true);">
                            <i class="fas fa-sync-alt"></i> <span>regen</span>
                        </button>
                        <div class="c-render-stage">
                            <img id="PlayerRender" src="/thumbs/player?id=<?= $user->id ?>" alt="Avatar render">
                        </div>
                    </div>

                    <div class="c-panel c-wearing" id="CurrentlyWearing">
                        <div class="c-wearing-head">
                            <h2>Currently Wearing</h2>
                            <p>Avatarındaki eşyalar</p>
                        </div>
                        <div id="AssetsContainer">
                            <div id="StatusText">
                                <b id="Loading">Loading...</b>
                                <span id="NoAssets" style="display:none">Nothing equipped.</span>
                            </div>
                            <table hidden></table>
                        </div>
                    </div>
                </aside>

                <!-- ═══ WORKSPACE ═══ -->
                <section class="c-workspace c-panel">
                    <nav class="c-tabs">
                        <a class="c-tab is-active" id="TabBodyColors" href="javascript:void(0)">Body Colors</a>
                        <a class="c-tab" id="TabAll" href="javascript:void(0)">All</a>
                    </nav>

                    <div class="c-content">

                        <!-- BODY COLORS -->
                        <section id="PanelBodyColors">
                            <p class="c-stage-label">Avatar Studio</p>
                            <h2 class="c-stage-title" id="BodyPartInfo">Body Colors</h2>
                            <p class="c-stage-desc">Bir parçaya tıkla, ardından aşağıdan rengini seç.</p>

                            <!-- Exact ID chain character.js uses -->
                            <div id="CharacterContainer">
                                <div id="BodyColours">
                                    <div id="BodyColoursContainer">
                                        <button type="button" data_bodytype="0" aria-label="Head"></button>
                                        <button type="button" data_bodytype="2" aria-label="Left Arm"></button>
                                        <button type="button" data_bodytype="1" aria-label="Torso"></button>
                                        <button type="button" data_bodytype="3" aria-label="Right Arm"></button>
                                        <button type="button" data_bodytype="4" aria-label="Left Leg"></button>
                                        <button type="button" data_bodytype="5" aria-label="Right Leg"></button>
                                    </div>
                                </div>
                            </div>

                            <div class="c-palette-box">
                                <p class="c-palette-title">Palette</p>
                                <p class="c-palette-desc">Parça seç → renk seç → otomatik kaydedilir.</p>
                                <div id="BodyColourPalette"></div>
                            </div>
                        </section>

                        <!-- ALL ITEMS -->
                        <section id="PanelAll" style="display:none;">
                            <div class="c-toolbar">
                                <div>
                                    <p class="c-stage-label">Wardrobe</p>
                                    <h2 class="c-toolbar-title">All Items</h2>
                                </div>
                                <div class="c-search">
                                    <input type="text" id="SearchBox" placeholder="Eşya ara...">
                                    <button class="btn-search" onclick="ANORRL.Character.Search()">Search</button>
                                </div>
                            </div>
                            <div id="Wardrobe">
                                <div id="AssetsContainer">
                                    <div id="StatusText">
                                        <b id="Loading">Loading assets...</b>
                                        <span id="NoAssets" style="display:none">Görünüşe göre boş!</span>
                                    </div>
                                    <table hidden></table>
                                    <div id="Paginator" class="c-paginator">
                                        <a class="c-page-btn" id="BackPager" href="javascript:ANORRL.Character.DeadvancePager()" style="display:none;">Back</a>
                                        <input type="text" id="NumberPutter" maxlength="3">
                                        <span>of <span id="Pages">1</span></span>
                                        <a class="c-page-btn" id="NextPager" href="javascript:ANORRL.Character.AdvancePager()" style="display:none;">Next</a>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>
                </section>
            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>

    <script>
    // ─────────────────────────────────────────────────────────────────
    // RENK SİSTEMİ — window.onload ile çalışır (character.js bittikten sonra)
    // character.js'in tüm event binding'lerini sökup sıfırdan bağlar.
    // ─────────────────────────────────────────────────────────────────
    window.addEventListener('load', function () {

        var selectedPart = '1'; // varsayılan: torso

        /* ── rgb string → #RRGGBB ── */
        function toHex(rgb) {
            if (!rgb || rgb.indexOf('rgb') === -1) return '';
            var m = rgb.match(/\d+/g);
            if (!m || m.length < 3) return '';
            return '#' + ('0' + parseInt(m[0]).toString(16)).slice(-2).toUpperCase()
                       + ('0' + parseInt(m[1]).toString(16)).slice(-2).toUpperCase()
                       + ('0' + parseInt(m[2]).toString(16)).slice(-2).toUpperCase();
        }

        /* ── brickColor id → hex (brickcolors tanımlı olmalı) ── */
        function colourIdToHex(id) {
            if (typeof brickcolors === 'undefined') return null;
            return Object.keys(brickcolors).find(function(h) { return brickcolors[h] === id; }) || null;
        }

        /* ── Part name ── */
        function partName(bt) {
            var map = {'0':'Head','1':'Torso','2':'Left Arm','3':'Right Arm','4':'Left Leg','5':'Right Leg'};
            return map[String(bt)] || 'Body';
        }

        /* ── Parçayı seç ── */
        function selectPart(bt) {
            selectedPart = String(bt);
            document.getElementById('BodyPartInfo').textContent = partName(bt);

            // Highlight body button
            document.querySelectorAll('#BodyColoursContainer button').forEach(function(b) {
                b.classList.toggle('part-selected', b.getAttribute('data_bodytype') === String(bt));
            });

            // Palette'de aktif rengi vurgula
            var btn = document.querySelector('#BodyColoursContainer button[data_bodytype="' + bt + '"]');
            var currentHex = btn ? toHex(btn.style.backgroundColor) : '';
            document.querySelectorAll('.cswatch').forEach(function(s) {
                s.classList.toggle('swatch-active', s.dataset.hex === currentHex);
            });
        }

        /* ── Rengi uygula + kaydet ── */
        function applyColour(hex) {
            var btn = document.querySelector('#BodyColoursContainer button[data_bodytype="' + selectedPart + '"]');
            if (btn) btn.style.backgroundColor = hex;

            // Swatch highlight
            document.querySelectorAll('.cswatch').forEach(function(s) {
                s.classList.toggle('swatch-active', s.dataset.hex === hex);
            });

            // Sunucuya kaydet
            function colourId(bt) {
                var b = document.querySelector('#BodyColoursContainer button[data_bodytype="' + bt + '"]');
                if (!b || !b.style.backgroundColor) return 1;
                var h = toHex(b.style.backgroundColor);
                return (typeof brickcolors !== 'undefined' && brickcolors[h]) ? brickcolors[h] : 1;
            }

            $.post('/api/character?r=setbodycolours', {
                head:     colourId(0),
                torso:    colourId(1),
                leftarm:  colourId(2),
                rightarm: colourId(3),
                leftleg:  colourId(4),
                rightleg: colourId(5)
            }, function() {
                ANORRL.Character.RenderPlayer();
            });
        }

        /* ── Palette'yi doldur ── */
        function buildPalette() {
            var palette = document.getElementById('BodyColourPalette');
            if (!palette) return;
            palette.innerHTML = '';
            if (typeof brickcolors === 'undefined') return;
            Object.keys(brickcolors).forEach(function(hex) {
                var s = document.createElement('button');
                s.className = 'cswatch';
                s.style.backgroundColor = hex;
                s.dataset.hex = hex;
                s.title = hex;
                s.addEventListener('click', function() { applyColour(hex); });
                palette.appendChild(s);
            });
        }

        /* ── Body butonlarına tıklama — character.js'inkini sök, yenisini bağla ── */
        function bindBodyButtons() {
            var btns = document.querySelectorAll('#BodyColoursContainer button[data_bodytype]');
            btns.forEach(function(btn) {
                // jQuery ile bağlanmış tüm click'leri kaldır
                $(btn).off('click').on('click', function() {
                    selectPart($(this).attr('data_bodytype'));
                });
            });
        }

        /* ── Mevcut renkleri API'den yükle ── */
        function loadBodyColours() {
            $.get('/api/character?r=getbodycolours', function(data) {
                if (!data || !data.colours) return;
                var c = data.colours;
                var map = {0:'head', 1:'torso', 2:'leftarm', 3:'rightarm', 4:'leftleg', 5:'rightleg'};
                Object.keys(map).forEach(function(bt) {
                    var hex = colourIdToHex(c[map[bt]]);
                    if (hex) {
                        var b = document.querySelector('#BodyColoursContainer button[data_bodytype="' + bt + '"]');
                        if (b) b.style.backgroundColor = hex;
                    }
                });
                selectPart('1'); // torso'yu seçili göster
            });
        }

        /* ── Tab switching ── */
        document.getElementById('TabBodyColors').addEventListener('click', function() {
            this.classList.add('is-active');
            document.getElementById('TabAll').classList.remove('is-active');
            document.getElementById('PanelBodyColors').style.display = '';
            document.getElementById('PanelAll').style.display = 'none';
        });

        document.getElementById('TabAll').addEventListener('click', function() {
            this.classList.add('is-active');
            document.getElementById('TabBodyColors').classList.remove('is-active');
            document.getElementById('PanelAll').style.display = '';
            document.getElementById('PanelBodyColors').style.display = 'none';
            ANORRL.Character.LoadWardrobe(null);
        });

        /* ── INIT ── */
        buildPalette();
        bindBodyButtons();
        loadBodyColours();
        ANORRL.Character.LoadCurrentlyWearing();
    });
    </script>
</body>
</html>