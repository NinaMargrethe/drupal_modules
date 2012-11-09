<?php
/**
 * Display help and module information
 * @param path which path of the site we're displaying help
 * @param arg array that holds the current path as would be returned from arg() function
 * @return help text for the path
 */
function gigsmodule_help($path, $arg) {
    $output = '';  //declare your output variable
    switch ($path) {
        case "admin/help#gigsmodule":
            $output = '<p>'.  t("Displays gigs sorted by date") .'</p>';
            break;
    }
    return $output;
}

function gigsmodule_perm() {
    return array('access gigsmodule content', 'administer gigsmodule content');
}

function gigsmodeule_block($op = 'list', $delta = 0, $edit = array()){
    $block = array();

    switch ($op){
        case 'list':
            $block[0]["info"] = t('Gigs module');
            break;

        case "view":
            $block_content = "";
            //TODO this code should fetch 10 gigs from database and print them in a ul list
            $today = getdate();
            $query_upcoming = "SELECT date, venue FROM {node} WHERE date >= '%d' ORDER BY CONVERT (datetime, date, 103) ASC LIMIT 0, 10";
            $upcoming_gigs =  db_query($query_upcoming, $today);

            while ($gigs = db_fetch_object($upcoming_gigs)) {
                $block_content .= l('<li>', $gigs, '</li>');
            }
            $block_content .= '<ul>' . $block_content . '</ul>';

            break;

        case "save":
            break;

        case "configure":
            break;
    }
    if($block_content == ''){
        $block_content = 'There are no upcoming gigs at the moment. Stay tuned!';
    }
    return $block;
}

function gigsmodule_all(){
    $page_content = '';

    //TODO create input form p39 Vaswani

    //persisting input to database
    $dbname = 'gigsdb';
    $tablename = 'gigstbl';
    $date_ = $_POST['date'];
    $time_ = $_POST['time'];
    $venue = $_POST['venue'];
    $age_limit = $_POST['age_limit'];
    $cc = $_POST['cc'];

    //TODO connect to server with proper authentication
    $connect = mysql_connect(server, uname, pw) or die ('Error: ' . mysql_error());

    $db_selected = mysql_select_db($dbname, $connect);
    if (!$db_selected) {
        mysql_query("CREATE DATABASE IF NOT EXISTS " . $dbname);
        $db_selected;
    }
    mysql_query("CREATE TABLE IF NOT EXISTS " .$tablename);

    //add gig
    $query = "INSERT INTO" . $tablename . " (date, time, venue, age_limit, cc,) VALUES ($date_, $time_, $venue, $age_limit, $cc)";
    mysql_query($query) or die ('Error updating database.');
    mysql_close();

    //show upcoming gigs
    $today = getdate();

    mysql_connect(server, uname, pw) or die ('Error: ' . mysql_error());
    mysql_select_db(gigs);

    $query_upcoming = "SELECT (date, time, venue, age_limit, cc) FROM {node} WHERE date >= '%d' ORDER BY CONVERT (datetime, date, 103) ASC";
    $upcoming_gigs =  db_query($query_upcoming, $today);

    //TODO change from list to table? [later]
    // http://stackoverflow.com/questions/2690668/how-to-create-html-tables-from-mysql/2690692#2690692
    //http://davidwalsh.name/html-mysql-php
    $page_content .= '<h3>Upcoming gigs</h3><ul>';
    while (db_fetch_object($query_upcoming)) {
        $page_content .= '<li>' . $upcoming_gigs . '</li>';
    }
    $page_content .= '</ul>';

    //show past gigs
    $query_previous = "SELECT date, venue, age_limit, FROM {node} WHERE date < '%d' ORDER BY CONVERT (datetime, date, 103) ASC ";
    $past_gigs =  db_query($query_previous, $today);


    //TODO change from list to table? [later]
    $page_content .= '<h3>Past gigs</h3><ul>';
    while (db_fetch_object($query_previous)) {
        $page_content .= '<li>' . $past_gigs . '</li>';
    }
    $page_content .= '</ul>';

    if($page_content == ''){
        $page_content = 'There are no upcoming gigs at the moment. Stay tuned!';
    }

    mysql_close();
    return $page_content;
}
function gigsmodule_add_gig(){
    //TODO form for adding gig should only be visible for authenticated user
}

function gigsmodule_edit_gig(){
    //TODO 'edit' and 'delete' functions for rows in upcoming
    //TODO buttons for 'edit' and 'delete' should only be visible for authenticated user
}

function gigsmodule_menu() {

    $items = array();

    $items['admin/settings/gigsmodule'] = array(
        'title' => 'Gigs module settings',
        'description' => 'Description of your gigs settings page',
        'page callback' => 'drupal_get_form',
        'page arguments' => array('gigsmodule_admin'),
        'access arguments' => array('access administration pages'),
        'type' => MENU_NORMAL_ITEM,
    );

    $items['gigstest'] = array(
        'title' => 'Gigs module',
        'page callback' => 'gigsmodule_all',
        'access arguments' => array('access gigsmodule content'),
        'type' => MENU_CALLBACK
    );

    return $items;
}