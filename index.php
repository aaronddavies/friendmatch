<?php

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
));

$basic = NULL;

$user_id = $facebook->getUser();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  //$likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

  // This fetches all of your friends.
  $friends = idx($facebook->api('/me/friends'), 'data', array());

  // And this returns 16 of your photos.
  //$photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
/*
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));*/
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());

$app_name = idx($app_info, 'name', '');

?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />

    <![if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]>

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo he($app_name); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('images/friendmatch_logo.jpg'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="The classic matching game for Facebook" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script type="text/javascript" src="javascript/jquery-1.7.1.min.js"></script>

    <script type="text/javascript">
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }

      $(function(){
        // Set up so we handle click on the buttons
        $('#postToWall').click(functstyle="float:right"ion() {
          FB.ui(
            {
              method : 'feed',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendToFriends').click(function() {
          FB.ui(
            {
              method : 'send',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendRequest').click(function() {
          FB.ui(
            {
              method  : 'apprequests',
              message : $(this).attr('data-message')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });
      });
    </script>

    <![if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]>
  </head>
  <body>
    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
// In IE this prevents logging in for some reason. Commenting out for now.
          //window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=418056044898733";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>

    <header class="clearfix">
      <?php if (isset($basic)) { ?>
      <p id="picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

      <div>
        <h1>Welcome to <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top"><?php echo he($app_name); ?></a>, the classic matching game for Facebook!</h1>
        <table class="threetagtable"><tr>
        <td class="threetag"><p id="tagline"><strong><a href="http://www.facebook.com/<?php echo he($user_id); ?>/" target="_top"><?php echo he(idx($basic, 'name')); ?></a></strong></p></td>
        <td class="threetag"><p id="gametime"><b><span id='gametime' style='background-color:white; color:navy'>&nbsp;</span></b></p></td>
        <td class="threetag"><p id="playagain"><span id='playagain' style='background-color:white; color:navy'>&nbsp;</span></p></td>
        </tr></table>
      </div>

        <!--div id="share-app">
          <p>Share this app with your friends!</p>
          <ul>
            <li>
              <a href="#" class="facebook-button" id="postToWall" data-url="<?php echo AppInfo::getUrl(); ?>">
                <span class="plus">Post to Wall</span>
              </a>
            </li>
            <li>
              <a href="#" class="facebook-button speech-bubble" id="sendToFriends" data-url="<?php echo AppInfo::getUrl(); ?>">
                <span class="speech-bubble">Send Message</span>
              </a>
            </li>
            <li>
              <a href="#" class="facebook-button apprequests" id="sendRequest" data-message="Test this awesome app">
                <span class="apprequests">Send Requests</span>
              </a>
            </li>
          </ul>
        </div-->   

      <?php } else { ?>
      <div>
        <h1>Welcome! Login to Facebook and then <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top">click here to play <?php echo he($app_name); ?>.</a></h1>
        <div class="fb-login-button" data-scope="user_likes,user_photos"></div>
      </div>
      <?php } ?>
    </header>

<?php
    if (isset($basic))
    {
?>
        <section id="card-area">
	        <table>
                <tr>
                    <td>
                        <table>
                            <?php
                                $numfr = 8; //number of friends in the game
                                $imgsz = 120; //square size of each card in pixels
                                for($i=0;$i<$numfr/2;$i++)
                                {	
	                                echo "<tr>";
	                                for($j=0;$j<$numfr/2;$j++)
	                                {
		                                echo "<td><img src=\"images/friendmatch_logo.jpg\" id=\"imgcard".($i * $numfr / 2 + $j)."\"width=\"".$imgsz."px\" height=\"".$imgsz."px\" style=\"position: relative\" alt=\"Click Me!\" /></td>";
	                                }
	                                echo "</tr>";
                                }
                                //<img src="images/friendmatch_logo.jpg" id="img1" width="150px" height="150px" style="position: relative;" alt="Click Me!" />
                            ?>
                        </table>
                    </td>
                    <td>
                        <div class="fb-like" data-href="https://apps.facebook.com/friendmatchappgame" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true" data-font="verdana"></div>
                        <!--div id="share-app"><a href="#" class="facebook-button" id="postToWall" data-url="<?php echo AppInfo::getUrl(); ?>"><span class="plus">Share this game!</span></a><br></div-->
                        <section id="instructions" class="instructions">
                            <span id="instructions-header" class="instructions-header">How to play</span>
                            <section id="instructions-text" class="instructions-text">
                                1. Click on any two blue cards to see if the same friend appears.
                                <br>2. If they match, your friend's name will appear in the list below.
                                <br>3. The timer begins as soon as you click the first card. Try to match them all as fast as you can!
                            </section>
                        </section>
                        <span id="instructions-tab-left" class="instructions-tab-left-active" onclick="showInstructions()"><a href="javascript:void(0)" onclick="showInstructions()">Instructions</a></span><span id="instructions-tab-right" class="instructions-tab-right-inactive" onclick="showHighScores()"><a href="javascript:void(0);" onclick="showHighScores()">High scores</a></span>
                        <br>
                        <span id='visit' style='background-color:white; color:black'>Matched friends:</span>
                        <br>
                        <script type="text/javascript">
                            CountActive = false;
                            GameOver = false;
                            DisplayFormat = "Your time: %%M%%:%%S%%%%N%%";
                        </script>
                        <script type="text/javascript" src="javascript/gametimer.js"></script>
			<span id='friendlist' style='background-color:white; color:black'>
                        <?php
                            for ($i=0;$i<$numfr;$i++)
                            {
                                echo "<p id=\"friend".$i."\"></p>";
                            }
                        ?>
			</span>
                    </td>
                </tr>
            </table>
        </section>
<?php
    }
?>

<section id="credits">
    <br>Written by <a href="http://www.facebook.com/aaron.d.davies/" target="_blank">Aaron Davies</a> and <a href="http://www.facebook.com/dave.rosca/" target="_blank">Dave Rosca</a>
</section>

<!-- GAME EXECUTION SECTION -->

    <?php
        // Extract a random set of friend id's		
		$id = array();
        $name = array();
        $rndKeys = array();
        $curIndex = 0;

        // Get a list of $numfr random friends
        $rndKeys = array_rand($friends, $numfr);
        
        // Duplicate each key for matching cards
        for ($i = 0; $i < $numfr; $i++)
        {
            $rndKeys[$numfr + $i] = $rndKeys[$i];
        }

        // Randomize the list of cards; store in $id
		while (count($rndKeys) > 0)
        {
			$rndIndex = rand(0, count($rndKeys) - 1);
            $rndkey = $rndKeys[$rndIndex];
			array_push($id, idx($friends[$rndkey], 'id'));
            array_push($name, idx($friends[$rndkey], 'name'));
            array_splice($rndKeys, $rndIndex, 1);
		}		 
    ?>

    <script type="text/javascript">
        var GameState = {
            ChooseFirstCard : {value: 0, name: "Choose First Card"},
            AnimateFirstCard : {value: 1, name: "Animate First Card"},
            ChooseSecondCard : {value: 2, name: "Choose Second Card"},
            AnimateSecondCard : {value: 3, name: "Animate Second Card"},
            RevertCards : {value: 4, name: "Revert Cards"},
            GameComplete: {value: 5, name: "Game Complete"}
        };
        
        var CardState = {
            NotFlipped: {value: 0, name: "Not Flipped"},
            Flipped: {value: 1, name: "Flipped"}
        };

        var RightTabState = {
            Instructions: {value: 0, name: "Instructions Tab"},
            InstructionsTransition: {value: 1, name: "Fading into Instructions"},
            HighScores: {value: 2, name: "High Scores Tab"},
            HighScoresTransition: {value: 3, name: "Fading into High Scores"},
            SubmitScore: {value: 4, name: "Submitting score"}
        };

        // Prefetch
        var cardFace = new Image();
        cardFace.src = "images/friendmatch_logo.jpg";
	    var imgs = new Array();
	    var myfriends = new Array();
        var myfriendsnames = new Array();
        var imgsz = <?php echo $imgsz; ?>;
	    var imgszhf = <?php echo ($imgsz/2); ?>;
	    var aspeed = 400; //animation speed of flipping a card

        var rightTabState = RightTabState.Instructions;
        var submittingScore = false;

	    <?php 
	        for ($i=0;$i<$numfr*2;$i++)
	        {
		        echo "myfriends[".$i."]=\"".$id[$i]."\";";
			    echo "myfriendsnames[".$i."]=\"".$name[$i]."\";";
                echo "imgs[".$i."] = new Image();";
		        echo "imgs[".$i."].src = \"https://graph.facebook.com/\" + myfriends[".$i."] + \"/picture?type=large\";";
	        }
        ?>

        var maxCards = <?php echo $numfr * 2; ?>;
        var cardStates = new Array();
        for (var i = 0; i < maxCards; i++) {
            cardStates[i] = CardState.NotFlipped;
        }
        var gameState = GameState.ChooseFirstCard;
        var consumedCards = 0;
        var firstFlippedCard;
        var nameprefix = "[id='friend";
        var fburlprefix = "<a href=\"http://www.facebook.com/";
        var ci;
        var finalscorems;
        var highScores = new Array(10);
        var highScoresNamesRemaining;
        var numHighScores;

        $(document).ready(
		    function() 
		    {
                $("[id^='imgcard']").click(
			        function() 
			        {if (CountActive == false && GameOver == false) {CountActive = true; ci = StartGameTimer();}
                        if (gameState == GameState.ChooseFirstCard || gameState == GameState.ChooseSecondCard) {
                            var id = parseInt(this.id.substring(7));
                            var cardState = cardStates[id];
                            if (cardState != CardState.Flipped) {
                                if (gameState == GameState.ChooseFirstCard)
                                    gameState = GameState.AnimateFirstCard;
                                else
                                    gameState = GameState.AnimateSecondCard;
                    	        $(this).animate({left: imgszhf, width: '0px'}, aspeed, function() {
                                    $(this).attr("src", imgs[id].src);
                                }).animate({
                                    left: '0px',
                                    width: imgsz
                                }, aspeed, function() { 
                                    switch (gameState) {
                                        case GameState.AnimateFirstCard:
                                            firstFlippedCard = id;
                                            gameState = GameState.ChooseSecondCard
                                            cardStates[id] = CardState.Flipped;
                                            break;
                                        case GameState.AnimateSecondCard:
                                            if (myfriends[firstFlippedCard] == myfriends[id]) {
                                                cardStates[id] = CardState.Flipped;
                                                consumedCards += 2;
						                        $(nameprefix.concat((consumedCards/2-1).toString(),"']")).html((fburlprefix).concat(myfriends[id],"\" target=\"_blank\">",myfriendsnames[id],"</a>"));
                                                if (consumedCards == maxCards) {
                                                    gameState = GameState.GameComplete;
							                        CountActive = false; GameOver = true; finalscorems = EndGameTimer(ci);
							                        document.getElementById("playagain").innerHTML = "<a href=\"<?php echo he(idx($app_info, 'link'));?>\" target=\"_top\">Click here to play again!</a>";
							                        document.getElementById("visit").innerHTML = "Visit your friends!"

                                                    // Submit score and display high scores
                                                    submitHighScore('<?php echo $user_id; ?>', '<?php echo $basic['name']; ?>', finalscorems.toString());
                                                }
                                                else {
                                                    gameState = GameState.ChooseFirstCard;
                                                }
                                            }
                                            else {
                                                $(this).animate({left: imgszhf, width: '0px'}, aspeed, function() {
                                                    $(this).attr("src", cardFace.src);
                                                }).animate({
                                                    left: '0px',
                                                    width: imgsz
                                                }, aspeed); 
                                                $("#imgcard" + firstFlippedCard.toString()).animate({left: imgszhf, width: '0px'}, aspeed, function() {
                                                    $("#imgcard" + firstFlippedCard.toString()).attr("src", cardFace.src);
                                                }).animate({
                                                    left: '0px',
                                                    width: imgsz
                                                }, aspeed); 
                                                gameState = GameState.ChooseFirstCard;
                                                cardStates[id] = CardState.NotFlipped;
                                                cardStates[firstFlippedCard] = CardState.NotFlipped;
                                            }
                                            break;
                                        default: break;
                                    }
                                }); 
                            }
                        }
                    } // function
                ); // .click

            });
    </script>
	<script type="text/javascript" src="javascript/finish.js"></script>
  </body>
</html>
