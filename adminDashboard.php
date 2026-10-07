<?php
require 'CapstoneSample_db.php';

$active = $pdo->query(
    "SELECT v.firstname, v.lastname, v.contact_number, l.building
     FROM visitor_log vl
     JOIN visitor v  ON v.visitor_id = vl.visitor_id
     LEFT JOIN location l ON l.log_id = vl.log_id
     WHERE vl.status = 'active'
     ORDER BY vl.date_time_in DESC"
)->fetchAll();

$activeCount = count($active);
$todayCount  = $pdo->query("SELECT COUNT(*) FROM visitor_log WHERE DATE(date_time_in) = CURDATE()")->fetchColumn();
$last        = $pdo->query(
    "SELECT CONCAT(v.firstname, ' ', v.lastname) FROM visitor_log vl
     JOIN visitor v ON v.visitor_id = vl.visitor_id
     ORDER BY vl.date_time_in DESC LIMIT 1"
)->fetchColumn();
$lastVisitor = $last ?: 'None yet';
$staffCount  = $pdo->query("SELECT COUNT(*) FROM `user` WHERE status = 'active' AND role <> 'admin'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
    .absTask a
    {
        color: white;
        text-decoration: none;
        margin-right: 2vw;
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
    body
    {
        overflow: hidden;
        margin: 3% 0 0 0;
    }
    .pagePar
    {
        display:flex;
        flex-direction: row;
        margin: 0;
        width: 100%;
        height: 100%;
        flex: 1 0 0;
        min-height: 100vh;
        gap:1vw;
    }
    .sectionA
    {
       gap: 1vh;
        flex: 1 1;
        min-height: 100%;
        overflow: scroll;
        max-height: 100vh;
    }
    .sectionB
    {
        flex: 1 1  ;
        min-height: 100%;
        display: flex;
        padding: 1vh;
        flex-direction: column;
        gap: 1vh;
    }
    .bChild
    {
        flex: 1 1;
        min-width: 100%;
        max-height: 5vh;
        gap: 5vh;
        align-content: center;
    }
    .bChildB
    {
        flex: 1 1;
        gap: 1vh;
        display: flex;
        flex-direction: column;
        
    }
    .tableActive
    {
        background-color: aliceblue;
        width: 100%;
        border-collapse: collapse;
        max-height: 80vh;
        overflow: scroll;
        text-align: center;
    }
    .tableActive th
    {
        background-color: blueviolet;
        color:white;
        padding: 1vh;
        border: black solid 0.1px;
        
    }
    .tableActive td
    {
        border: black solid 0.1px;
        padding: 1vh;
    }
    .infoCardPar
    {
        flex: 1 1;
        max-height: 45%;
        align-content: center;
        
    }
    .infoCardChi
    {
    background-color: white;
    border: #4a265e solid 2px;
    border-radius: 12px; 
    color: black;
    width:100%;
    min-height: 90%;
    padding: 3vh;
    box-sizing: border-box;
    margin: auto;
    box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.3);
    align-content: baseline;
    }
</style>
</head>
<body>
    <div class="absTask">
        <a href="adminDashboard.php">Dashboard</a>
        <a href="logbookForm.html">New Visitor</a>
    </div>
    <div class="pagePar">
        <div class="sectionA">
            <table class="tableActive">
                <tr>
                    <th>Visitor Name</th>
                    <th>Contact Number</th>
                    <th>Nearest Location</th>
                </tr>
                <?php if (!$active): ?>
                <tr><td colspan="3">No active visitors</td></tr>
                <?php endif; ?>
                <?php foreach ($active as $row): ?>
                <tr>
                    <td><?= e($row['firstname'] . ' ' . $row['lastname']) ?></td>
                    <td><?= e($row['contact_number']) ?></td>
                    <td><?= e($row['building'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="sectionB">
            <div class="bChild">
                <button type="button">See all Visitors</button>
                <button type="button">See all Records</button>
            </div>
            <div class="bChildB">
                <div class="infoCardPar">
                    <div class="infoCardChi">
                        <h3>Active Visitor Count: <?= (int)$activeCount ?></h3>
                        <h3>Total Visitors Today: <?= (int)$todayCount ?></h3>
                        <h3>Last Visitor Admitted: <?= e($lastVisitor) ?></h3>
                        <h3>Monitoring Staff: <?= (int)$staffCount ?></h3>
                    </div>
                </div>
                <div class="infoCardPar">
                    <div class="infoCardChi">
                        <h3>Announcement:</h3>
                        [Message]
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>