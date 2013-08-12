<?php

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

require_once 'app/facebook.php';
require_once 'app/utils.php';


$lat = "-34.725071";
$long = "135.881030";

// using offset gives us a "square" on the map from where to search the events
$offset = 0.4;

$user_id = $facebook->getUser();
if ($user_id) {
    try {
        // Fetch the viewer's basic information
        $basic = $facebook->api('/me');
    } catch (FacebookApiException $e) {
        // If the call fails we check if we still have a user. The user will be
        // cleared if the error is because of an invalid accesstoken
        if (!$facebook->getUser()) {
            header('Location: ' . AppInfo::getUrl($_SERVER['REQUEST_URI']));
            exit();
        }
    }

    // This fetches some things that you like . 'limit=*" only returns * values.
    // To see the format of the data you are retrieving, use the "Graph API
    // Explorer" which is at https://developers.facebook.com/tools/explorer/
    $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

    // This fetches 4 of your friends.
    $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());

    // And this returns 16 of your photos.
    $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

    // Here is an example of a FQL call that fetches all of your friends that are
    // using this app
    $app_using_friends = $facebook->api(array(
        'method' => 'fql.query',
        'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
    ));



    $events = 'SELECT pic_big, name, venue, location, start_time, eid, description, start_time, end_time FROM event WHERE eid IN (SELECT eid FROM event_member WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me()) OR uid = me()) AND venue.longitude < \'' . ($long + $offset) . '\' AND venue.latitude < \'' . ($lat + $offset) . '\' AND venue.longitude > \'' . ($long - $offset) . '\' AND venue.latitude > \'' . ($lat - $offset) . '\' ORDER BY start_time ASC ';
    $events2 = $facebook->api(array(
        'method' => 'fql.query',
        'query' => $events
    ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/' . AppInfo::appID());

$app_name = idx($app_info, 'name', '');

?>
    <!DOCTYPE html>
    <html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes"/>

        <title><?php echo he($app_name); ?></title>
        <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css"/>
        <link rel="stylesheet" href="stylesheets/mobile.css"
              media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)"
              type="text/css"/>

        <!--[if IEMobile]>
        <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"/>
        <![endif]-->

        <!-- These are Open Graph tags.  They add meta data to your  -->
        <!-- site that facebook uses when your content is shared     -->
        <!-- over facebook.  You should fill these tags in with      -->
        <!-- your data.  To learn more about Open Graph, visit       -->
        <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
        <meta property="og:title" content="<?php echo he($app_name); ?>"/>
        <meta property="og:type" content="website"/>
        <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>"/>
        <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>"/>
        <meta property="og:site_name" content="<?php echo he($app_name); ?>"/>
        <meta property="og:description" content="My first app"/>
        <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>"/>

        <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>

        <script type="text/javascript">
            function logResponse(response) {
                if (console && console.log) {
                    console.log('The response was', response);
                }
            }

            $(function () {
                // Set up so we handle click on the buttons
                $('#postToWall').click(function () {
                    FB.ui(
                        {
                            method: 'feed',
                            link: $(this).attr('data-url')
                        },
                        function (response) {
                            // If response is null the user canceled the dialog
                            if (response != null) {
                                logResponse(response);
                            }
                        }
                    );
                });

                $('#sendToFriends').click(function () {
                    FB.ui(
                        {
                            method: 'send',
                            link: $(this).attr('data-url')
                        },
                        function (response) {
                            // If response is null the user canceled the dialog
                            if (response != null) {
                                logResponse(response);
                            }
                        }
                    );
                });

                $('#sendRequest').click(function () {
                    FB.ui(
                        {
                            method: 'apprequests',
                            message: $(this).attr('data-message')
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

        <!--[if IE]>
        <script type="text/javascript">
            var tags = ['header', 'section'];
            while (tags.length)
                document.createElement(tags.pop());
        </script>
        <![endif]-->
        <script type="text/javascript"
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCqA6HexVvYxjlw2kSoIc_K1dQJeHSnB8&sensor=true">
        </script>
        <script type="text/javascript">
            function initialize() {
                var mapOptions = {
                    center: new google.maps.LatLng(<?php echo $lat;?>, <?php echo $long;?>),
                    zoom: 8,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                var map = new google.maps.Map(document.getElementById("map-canvas"),
                    mapOptions);
                <?php foreach($events2 as $event):?>

                var marker<?php echo $event['eid'];?> = new google.maps.Marker({
                    position: new google.maps.LatLng(<?php echo $event['venue']['latitude'];?>,<?php echo $event['venue']['longitude'];?>),
                    map: map,
                    title:"<?php echo he($event['name']);?>"
                });

                google.maps.event.addListener(marker<?php echo $event['eid'];?>, 'click', function() {
                    loadInfoBox('<?php echo $event['eid'];?>');
                });
                <?php endforeach;?>
            }
            function loadInfoBox(eid) {

            }
            google.maps.event.addDomListener(window, 'load', initialize);
        </script>
    </head>
    <body>
    <div id="fb-root"></div>
    <script type="text/javascript">
        window.fbAsyncInit = function () {
            FB.init({
                appId: '<?php echo AppInfo::appID(); ?>', // App ID
                channelUrl: '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
                status: true, // check login status
                cookie: true, // enable cookies to allow the server to access the session
                xfbml: true // parse XFBML
            });

            // Listen to the auth.login which will be called when the user logs in
            // using the Login button
            FB.Event.subscribe('auth.login', function (response) {
                // We want to reload the page now so PHP can read the cookie that the
                // Javascript SDK sat. But we don't want to use
                // window.location.reload() because if this is in a canvas there was a
                // post made to this page and a reload will trigger a message to the
                // user asking if they want to send data again.
                window.location = window.location;
            });

            FB.Canvas.setAutoGrow();
        };

        // Load the SDK Asynchronously
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    <script type="text/javascript">
    </script>
    <header class="clearfix">
        <?php if (isset($basic)) { ?>
            <p id="picture"
               style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

            <div>
                <h1>Welcome, <strong><?php echo he(idx($basic, 'name')); ?></strong></h1>

                <p class="tagline">
                    This is your app
                    <a href="<?php echo he(idx($app_info, 'link')); ?>" target="_top"><?php echo he($app_name); ?></a>
                </p>

                <div id="share-app">
                    <p>Share your app:</p>
                    <ul>
                        <li>
                            <a href="#" class="facebook-button" id="postToWall"
                               data-url="<?php echo AppInfo::getUrl(); ?>">
                                <span class="plus">Post to Wall</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="facebook-button speech-bubble" id="sendToFriends"
                               data-url="<?php echo AppInfo::getUrl(); ?>">
                                <span class="speech-bubble">Send Message</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="facebook-button apprequests" id="sendRequest"
                               data-message="Test this awesome app">
                                <span class="apprequests">Send Requests</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php } else { ?>
            <div>
                <h1>Welcome</h1>

                <div class="fb-login-button" data-scope="user_likes,user_photos"></div>
            </div>
        <?php } ?>
    </header>

<!--    <section id="get-started">
        <p>AAA Welcome to your Facebook app, running on <span>heroku</span>!</p>
        <a href="https://devcenter.heroku.com/articles/facebook" target="_top" class="button">Learn How to Edit This
            App</a>
    </section>
-->

    <section id="map" class="clearfix">
        <div id="map-canvas" style="width: 760px; height: 570px"></div>
    </section>
    </body>
    </html>
<!--<?php print_r($events2);?>-->