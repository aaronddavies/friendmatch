<?php
    require_once("DBInfo.php");    

    switch ($_POST['func'])
    {
        case '1':
            echo getHighScores();
            break;
        case '2':
            echo submitHighScore();
            break;
        default:
            fail();
            break;
    }

    function connectToDB()
    {
        global $DB_HOST, $DB_USER, $DB_PASS, $DB_PORT, $DB_DATABASE;

        $db = mysql_connect("$DB_HOST:$DB_PORT", $DB_USER, $DB_PASS);
        if (!$db)
        {
            //echo mysql_error($db); // debug
            fail();
        }

        mysql_select_db($DB_DATABASE, $db);
        return $db;
    }

    function disconnectFromDB($db)
    {
        mysql_close($db);
    }

    function getHighScores()
    {
        $db = connectToDB();
        $queryString = "
            SELECT best_scores.facebook_id, facebook_name, score_time
            FROM 
                (SELECT * FROM friendmatch_highscores ORDER BY score_time ASC) AS `best_scores`
            INNER JOIN friendmatch_users ON friendmatch_users.facebook_id = best_scores.facebook_id
	    GROUP BY facebook_id
            ORDER BY score_time ASC
            LIMIT 10
        ";

        $result = query($db, $queryString);
        disconnectFromDB($db);

        $data = array();
        $score = mysql_fetch_array($result);
        while ($score)
        {
            array_push($data, array("facebook_id" => $score['facebook_id'], "facebook_name" => $score['facebook_name'], "score_time" => $score['score_time']));
            $score = mysql_fetch_array($result);
        }

        return json_encode($data);
    }

    function submitHighScore()
    {
        if (!isset($_POST['facebook_id']) || !isset($_POST['facebook_name']) || !isset($_POST['score_time']))
            fail();

        $db = connectToDB();
        $queryString = "
            INSERT IGNORE INTO friendmatch_users (facebook_id, facebook_name)
            VALUES ('$_POST[facebook_id]', '$_POST[facebook_name]')
        ";
        query($db, $queryString);

        $queryString = "
            INSERT INTO friendmatch_highscores (facebook_id, score_time)
            VALUES ('$_POST[facebook_id]', '" . $_POST['score_time'] / 1000.0 . "')
        ";
        query($db, $queryString);
        
        return json_encode(TRUE);
    }

    function query($db, $queryString)
    {
        $result = mysql_query($queryString, $db);
        if (!$result)
        {
            //echo mysql_error($db); // debug
            disconnectFromDB($db);
            fail();
        }
        return $result;
    }

    function fail()
    {
        echo json_encode(FALSE);
        exit();
    }
?>
