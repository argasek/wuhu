<?
if (!defined("ADMIN_DIR")) exit();

$voter = SpawnVotingSystem();

if (!$voter)
    die("<div class='alert alert-dismissible alert-danger'>VOTING SYSTEM ERROR</div>");

$csrf = new CSRFProtect();

if ($_POST["vote"]) {
    $a = array();
    if ($csrf->ValidateToken()) {
        if ($voter->SaveVotes()) {
            echo "<div class='alert alert-dismissible alert-success fade in'>Votes saved!</div>";
        } else {
            echo "<div class='alert alert-dismissible alert-danger'>There was an error saving your votes!</div>";
        }
    } else {
        echo "<div class='alert alert-dismissible alert-danger fade in'>Your CSRF token expired!</div>";
    }
}

global $query;
$query = new SQLSelect();
$query->AddTable("compos");
$query->AddWhere("votingopen > 0");
$query->AddOrder("start");
run_hook("vote_prepare_dbquery", array("query" => &$query));
$compos = SQLLib::selectRows($query->GetQuery());

if ($compos) {
    echo '<div class="alert alert-dismissible alert-info fade in alert-voting"><b>Please remember: </b> your vote is valid only when you click <b>&quot;Submit votes!&quot;</b> button on the bottom of the page!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>';
    echo "<form id='votingform' action='" . $_SERVER['REQUEST_URI'] . "' method='post' enctype='multipart/form-data'>\n";
    $csrf->PrintToken();

    foreach ($compos as $compo) {
        global $query;
        $query = new SQLSelect();
        $query->AddTable("compoentries");
        $query->AddWhere(sprintf_esc("compoid=%d", $compo->id));
        $query->AddOrder("playingorder");
        run_hook("vote_compo_dbquery", array("query" => &$query));
        $entries = SQLLib::selectRows($query->GetQuery());

        if ($entries) {
            printf("<h3>%s</h3>\n", $compo->name);

            echo "<div class='votelist row'>\n";
            $voter->PrepareVotes($compo);

            foreach ($entries as $entry) {
                echo "<div class='col-xs-12 col-md-6 col-lg-4'>\n";

                echo "<div class='vote-entry'>\n";

                echo "<div class='vote-entry-voter row'>";
                $title = _html($entry->title);
                $author = _html($entry->author);
                if ($compo->showauthor)
                    printf("<div class='title col-xs-12' title='%s'><b>%s</b><span>%s</span></div>\n", $title . ' - ' . $author, $title, $author);
                else
                    printf("<div class='title col-xs-12' title='%s'><b>%s</b></div>\n", $title, $title);

                echo "<div class='col-xs-6'>";
                echo "<h4 class='vote-entry-playing-order'>#" . $entry->playingorder . "</h4>\n";
                echo "</div>";

                echo "<div class='col-xs-6'>";
                printf("<div class='vote'>\n");
                $voter->RenderVoteGUI($compo, $entry);
                printf("</div>\n");
                echo "</div>";
                echo "</div>";


                printf("<div class='vote-entry-screenshot'><a href='screenshot.php?id=%d' target='_blank'><img src='screenshot.php?id=%d&amp;show=thumb'/></a></div>\n", $entry->id, $entry->id);
                echo "</div>\n";
                echo "</div>\n";
            }
            echo "</div>\n";
        }
    }
    echo '<div id="votesubmit"><button class="btn btn-primary btn-default" type="submit" value="Submit votes!">Submit votes!</button></div>';
    echo "\n";
    echo "</form>\n";
}


?>

<script>
    // When document is ready replaces the need for onload
    jQuery(function ($) {

        // Grab your button (based on your posted html)
        $('.alert-voting button').click(function (e) {

            // Do not perform default action when button is clicked
            // e.preventDefault();

            /* If you just want the cookie for a session don't provide an expires
             Set the path as root, so the cookie will be valid across the whole site */
            Cookies.set('alert-voting', 'closed', {path: '/'});

        });

        if (Cookies.get('alert-voting') === 'closed') {
            $('.alert-voting').hide();
        }

    });
</script>
