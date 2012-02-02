<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>{$sitetitle}</title>
<link rel="stylesheet" href="css/bootstrap.css" />
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript">
{literal}
var PRG = {
	delkey:function(id){
		$.get('api.php', { method: 'deleteKeyword', keyword_id: id } ,function(data){
			if(data.match(/^error/i)){
				alert(data);
			}else{
				$('#keyid_' + id).hide();
			}
		});
	}
}
{/literal}
</script>
<style type="text/css">
{literal}
a {text-decoration:none;}

table#reservation_table {
    width: 800px;
    border: 1px #BBB solid;
    border-collapse: collapse;
    border-spacing: 0;
}

table#reservation_table th {
    padding: 5px;
    border: #E3E3E3 solid;
    border-width: 0 0 1px 1px;
    background: #BBB;
    font-weight: bold;
    line-height: 120%;
    text-align: center;
}
table#reservation_table td {
    padding: 5px;
    border: 1px #BBB solid;
    border-width: 0 0 1px 1px;
    text-align: center;
}

table#reservation_table tr.ctg_news, #category_select a.ctg_news {background-color: #FFFFD8;}
table#reservation_table tr.ctg_etc, #category_select a.ctg_etc {background-color: #FFFFFF;}
table#reservation_table tr.ctg_information, #category_select a.ctg_information {background-color: #F2D8FF;}
table#reservation_table tr.ctg_sports, #category_select a.ctg_sports {background-color: #D8FFFF;}
table#reservation_table tr.ctg_cinema, #category_select a.ctg_cinema {background-color: #FFD8D8;}
table#reservation_table tr.ctg_music, #category_select a.ctg_music {background-color: #D8D8FF;}
table#reservation_table tr.ctg_drama, #category_select a.ctg_drama {background-color: #D8FFD8;}
table#reservation_table tr.ctg_anime, #category_select a.ctg_anime {background-color: #FFE4C8;}
table#reservation_table tr.ctg_variety, #category_select a.ctg_variety {background-color: #FFD2EB;}
table#reservation_table tr.ctg_10, #category_select a.ctg_10 {background-color: #E4F4F4;}
table#reservation_table tr.prg_rec  {background-color: #F55;color:#FEE}
{/literal}
</style>
</head>
<body>

<div class="container">
<div class="topbar">
  <div class="fill">
    <div class="container">
      <a class="brand" href="#">{$sitetitle}</a>
      <ul class="nav">
        <li><a href="search.php">番組検索</a></li>
        <li><a href="reservationTable.php">予約一覧</a></li>
      </ul>
    </div>
  </div>
</div>

  <div class="page-header">
    <p>$weekofdays = array( "月", "火", "水", "木", "金", "土", "日", "なし" );</p>
  </div>
  <div class="row">
{if count($keywords)}
<table id="reservation_table">
 <tr>
  <th>id</th>
  <th>検索語句</th>
  <th>正規表現</th>
  <th>種別</th>
  <th>局</th>
  <th>カテゴリ</th>
  <th>曜日</th>
  <th>録画モード</th>
  <th>削除</th>
 </tr>

{foreach from=$keywords item=keyword}
 <tr id="keyid_{$keyword.id}">
  <td><a href="recordedTable.php?key={$keyword.id}">{$keyword.id}</a></td>
  <td><a href="recordedTable.php?key={$keyword.id}">{$keyword.keyword|escape}</a></td>
  <td>{if $keyword.use_regexp}使う{else}使わない{/if}</td>
  <td>{$keyword.type}</td>
  <td>{$keyword.channel}</td>
  <td>{$keyword.category}</td>
  <td>{$keyword.weekofday}</td>
  <td>{$keyword.autorec_mode}</td>
  <td><input type="button" value="削除" onClick="javascript:PRG.delkey('{$keyword.id}')" /></td>
 </tr>
{/foreach}
</table>
{else}
  キーワードはありません
{/if}
  </div>
</div>

</body>
</html>
