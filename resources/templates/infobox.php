
<h2 id="firstHeading" class="firstHeading"><?php echo he($event['name']); ?></h2>
<div id="bodyContent">
    <img style="float:right;padding-left:10px;padding-bottom:10px;max-width:100px"
         src="<?php echo he($event['pic_big']); ?>"/>

    <p><b>Location:</b> <?php echo he($event['location']); ?></p>

    <p><b>Start Time: </b><?php echo he($event['start_time']); ?></p>

    <p><b>End Time: </b><?php echo he($event['end_time']); ?></p>

    <p><?php echo str_replace(array("\r\n", "\n", "\r"), '<br />', he($event['description'])); ?></p>
</div>
