<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/Settings.class.php" );

if( isset($_GET['channel_disc']) ) {
	
	try {
		$crec = new DBRecord( CHANNEL_TBL, "channel_disc", $_GET['channel_disc'] );
		
		echo '<div class="prg_title">';
		echo $crec->name . "</div>";
		
		// 種別
		echo '<div class="prg_channel"><span class="labelLeft">種別：</span><span class="bold">';
		echo $crec->type;
		echo '</span></div>';
		
		// チャンネル
		echo '<div class="prg_channel"><span class="labelLeft">物理チャンネル：</span><span class="bold">';
		echo $crec->channel;
		echo '</span></div>';
		
		// フォーム
		echo '<form method="post" action="channelSetSID.php">';
		echo '<div class="prg_channel"><span class="labelLeft">サービスID：</span>';
		echo '<span><input type="text" name="n_sid" size="20" id="id_sid" value="'. $crec->sid .'" /></span>';
		echo '<input type="hidden" name="n_channel_disc" id="id_disc" value="'. $crec->channel_disc .'" />';
		echo '</div>';
		echo '</form>';
	}
	catch( Exception $e ) {
		echo "error:チャンネル情報の取得に失敗";
	}
}
?>