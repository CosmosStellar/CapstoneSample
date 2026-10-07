<?php
require 'CapstoneSample_db.php';

$logId = isset($_GET['log_id']) ? (int)$_GET['log_id'] : 0;
$info  = null;

if ($logId > 0) {
    $stmt = $pdo->prepare(
        "SELECT v.firstname, v.middlename, v.lastname, v.birthdate, v.visitor_type, l.building
         FROM visitor_log vl
         JOIN visitor v ON v.visitor_id = vl.visitor_id
         LEFT JOIN location l ON l.log_id = vl.log_id
         WHERE vl.log_id = ?"
    );
    $stmt->execute([$logId]);
    $info = $stmt->fetch() ?: null;
}

$fullName  = $info ? trim($info['firstname'] . ' ' . ($info['middlename'] ?? '') . ' ' . $info['lastname']) : '-';
$birthdate = ($info && $info['birthdate']) ? date('F j, Y', strtotime($info['birthdate'])) : '-';
$age       = ($info && $info['birthdate']) ? (new DateTime($info['birthdate']))->diff(new DateTime('today'))->y : '-';
$type      = $info['visitor_type'] ?? '-';
$building  = $info['building'] ?? '';

// Fill in your own descriptions here (key = building name)
$buildingDesc = [
    'Admin Building' => '', 'FDT' => '', 'Anatomy Building' => '', 'Arch' => '', 'PGT Building' => '',
    'GPL Building' => '', 'LRC Building' => '', 'Purple Owl Complex' => '', 'Centennial Gymnasium' => '',
];
$desc = $buildingDesc[$building] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
           #map
        {
            width: 80%;
            height: 80%;
            margin: auto;
            border: solid 10px #4a265e;
            z-index: 1;
            transition: width 1s ease, height 1s ease;
        }
            #map:hover
            {
                width: 85%;
                height: 85%;
            }
       
        body
        {
            margin: 0 0 0 0;
           
        }
         .absTask a
    {
        color: white;
    }
     .absTask
    {
        
        color: white;
        position: absolute;
        top: 0vh;
        width:100%;
        height: 5%;
        background-color: #4a265e;
        transition: height 2s ease;
        align-content: center;
        padding-left: 1vw;
    }
      .absTask:hover
    {
        position: absolute;
        top: 0vh;
        width:100%;
        height: 10%;
        background-color: #4a265e;
    }
        .classR
        {
        display: flex;
        flex-direction: row;
        width: 100%;  
        height: 100%;
        min-height: 100%;
        flex: 1 1 auto;
        overflow: hidden;
        }
        .classC
        {
        display: flex;
        flex-direction: column;
         width: 100%;  
        height: 100%;
        min-height: 100%;
        flex: 1 1 auto;
        overflow: hidden;
        }
        .divc
        {
            flex: 1 1 0px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
       
        .divcB
        {
            flex: 0.9 0px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: flex 1s ease;
            
        }
        .divcB:hover
        {
            flex: 1 0 0;
        }
        .pRibbon
        {
            display: flex;
            flex-direction: column;
            width: 100vw;  
            height: 100vh;
            min-height: 100vh;
        }
        .tRibbon
        {
           background-color: #4a265e;
           min-height: 5vh; 
           max-height: 8vh;
            width: 100%;
        z-index: 9999; 
        }
    
    .contDes
    {
    background-color: white;
    border: #4a265e solid 2px;
    border-radius: 12px; 
    color: black;
    width:80%;
    min-height: 30vh;
    padding: 3vh;
    box-sizing: border-box;
    margin: auto;
    box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.3);
    }
      .contDesAlt
        {
            margin-top: 0px;
            min-height: 42VH;
        }
    .idParent
    {
        display: flex;
        flex-direction: row;
        min-height: 100%;
        max-height: 100%;
        align-items: flex-start;
        gap:1vh;
    }
    .idImgcont
    {
       
       width: 30%;
        overflow: hidden;
        display: flex;
        align-items: flex-start; 
    }
    .idImg
    {
    width: 90%;
    height: auto; 
    aspect-ratio: 1 / 1; 
    object-fit: cover;
    object-position: center;
    flex-shrink: 0; 
    border: solid black 1px;
    }
    .idInfo
    {
        color:black;
        display: flex;
        flex-direction: column;
        flex: 1 1;
      padding: 0%;
    }
    .idInfoChild
    {
        padding: 0;
        line-height: 5vh;
        font-size: large;
         align-items: flex-start;
    }
    .idInfoCont
    {
    color: black;
    background-color: lightgrey;
    height: 2vh;
    }
    </style>
</head>
<body>
    <div class="pRibbon">
        <div class="absTask">
            <a href="visitorDashboard.php<?= $logId ? '?log_id=' . $logId : '' ?>">Dashboard</a>
        </div>
        <div class="classR">
            <div class="divc">
                <div id="map"></div>
            </div>
            <div class="divc">
                <div class="divcB">
                    <div class="contDes">
                        <h2>Building Name: <?= e($building ?: '-') ?></h2>
                        <br>
                        <h3>Building Desc: <?= e($desc ?: 'No description yet.') ?></h3>
                    </div>
                </div>
                <div class="divcB">
                    <div class="contDes contDesAlt">
                        <div class="idParent">
                            <div class="idInfo">
                                <h3>VISITOR INFORMATION CARD</h3>
                                <div class="idInfoChild">Name:</div>
                                <div class="idInfoCont"><?= e($fullName) ?></div>
                                <div class="idInfoChild">Age:</div>
                                <div class="idInfoCont"><?= e($age) ?></div>
                                <div class="idInfoChild">Birthdate:</div>
                                <div class="idInfoCont"><?= e($birthdate) ?></div>
                                <div class="idInfoChild">Visitor Type:</div>
                                <div class="idInfoCont"><?= e($type) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const myBuilding = <?= json_encode($building) ?>;

        const campusBounds = L.latLngBounds(
            [14.661016559725885, 120.98713847292149],
            [14.657335733123084, 120.98542608229778]
        );

        const map = L.map('map', {
            minZoom: 17.5,
            maxZoom: 24,
            maxBounds: campusBounds,
            maxBoundsViscosity: 1.0
        }).setView([14.65835117638478, 120.98624458460917], 20);

        L.tileLayer('https://api.maptiler.com/maps/openstreetmap/{z}/{x}/{y}.jpg?key=76drzXxYrFFwuQRnFNKS', {
            attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
            minZoom: 17.5,
            maxZoom: 22,
            maxNativeZoom: 19
        }).addTo(map);

        const leafletIcon = L.icon({
            iconUrl: 'https://64.media.tumblr.com/193610c5a76a3e0801f40790981a2816/a50f3003a25ecf54-d3/s400x600/adca53d6e4fc0485806a187e2918672c2ffebe74.pnj',
            iconSize: [33, 29]
        });

        // names must match the Building dropdown values in logbookForm.html
        const buildings = [
            ['Admin Building',        14.657660229212729, 120.98671314851723],
            ['FDT',                   14.65834967909162,  120.98625004709956],
            ['Anatomy Building',      14.658000163009532, 120.9867319239792],
            ['Arch',                  14.65776532330548,  120.98641274112585],
            ['PGT Building',          14.659267858564103, 120.98567713880672],
            ['GPL Building',          14.659045187468603, 120.98609128594734],
            ['LRC Building',          14.659267123462485, 120.98645539027785],
            ['Purple Owl Complex',    14.66049141773283,  120.98591887871622],
            ['Centennial Gymnasium',  14.660531785242888, 120.98636658748819]
        ];

        const markers = {};
        buildings.forEach(function (b) {
            markers[b[0]] = L.marker([b[1], b[2]], { icon: leafletIcon }).addTo(map).bindPopup(b[0]);
        });

        // zoom to the building the visitor picked
        let focusedOnBuilding = false;
        if (myBuilding && markers[myBuilding]) {
            map.setView(markers[myBuilding].getLatLng(), 20);
            markers[myBuilding].openPopup();
            focusedOnBuilding = true;
        }

        L.control.scale({ metric: true }).addTo(map);

        // live location: one marker/circle that moves, not a new one every update
        let userMarker = null, userCircle = null, centered = false, alerted = false;

        function success(pos) {
            const latlng = [pos.coords.latitude, pos.coords.longitude];
            if (!userMarker) {
                userMarker = L.marker(latlng).addTo(map);
                userCircle = L.circle(latlng, { radius: pos.coords.accuracy }).addTo(map);
            } else {
                userMarker.setLatLng(latlng);
                userCircle.setLatLng(latlng).setRadius(pos.coords.accuracy);
            }
            // center once, only if the visitor is inside the campus area
            if (!centered && !focusedOnBuilding && campusBounds.contains(latlng)) {
                map.setView(latlng, 20);
                centered = true;
            }
        }

        function error(err) {
            if (alerted) return;   // watchPosition repeats errors; only warn once
            alerted = true;
            if (err.code === 1) {
                alert("Please allow access for the geolocation");
            } else {
                alert("Cannot get current location");
            }
        }

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(success, error);
        }
    </script>
</body>
</html>