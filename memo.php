<?php

/* エスケープ関数 */
function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function eh($v){ echo h($v); }
/* brタグの有効化 */
function br2tag($v){

	/* エスケープされた<br>を戻す */
	return str_replace('&lt;br&gt;','<br>',$v);
}

/* 保存ファイル名 */
define('SAVE_NAME','memo.txt');

/* ユニークなID */
$id = uniqid();
/* 日時 */
$date = date('Y/m/d H:i');
/* テキスト */
$text = '';

/* 保存済データを読込 */
if (!file_exists(SAVE_NAME)) touch(SAVE_NAME);
$lines = file_get_contents(SAVE_NAME);

if ($_SERVER['REQUEST_METHOD']=='POST') {
	/* $_POST の中にPOST入力が入る */
	if (!empty($_POST['text'])) {

		$text = $_POST['text'];

		/* 改行コードの統一 */
		$text = str_replace("\r\n","\n",$text);
		$text = str_replace("\r","\n",$text);
		/* 改行コードを改行タグに */
		$text = str_replace("\n","<br>",$text);
		/* 区切り文字を除去 */
		$text = str_replace("\t","",$text);

		/* 新規に登録するデータ */
		$line = $id."\t".$date."\t".$text."\n";

		/* 新規データの後ろに保存済データを追加更新、保存 */
		$lines = $line . $lines;
		file_put_contents(SAVE_NAME, $lines);

	/* $_POST['id'] に削除対象のIDが入る */
	} elseif(isset($_POST['id'])) {

		$new = '';
		foreach(explode("\n",$lines) as $line){

			if( strpos($line,"\t")===false ) continue;

			list($id,$date,$text) = explode("\t",$line);

			if(in_array($id,$_POST['id'])) continue;

			$new .= $line."\n";
		}
		file_put_contents(SAVE_NAME, $new);
	}

	header('Location: '.$_SERVER['SCRIPT_NAME']);
	exit;
}

/* 出力用の保存データ */
$DATA = array();

/* 出力用に代入 */
foreach(explode("\n",$lines) as $line){

	if( strpos($line,"\t")===false ) continue;

	list($id,$date,$text) = explode("\t",$line);

	$DATA[] = array(
		'id'=>$id,
		'date'=>$date,
		'text'=>$text
	);
}

?>
<html>
<meta charset="utf-8">
<title>簡易メモ帳</title>
<style>
table {
	width: 100%;
}
form {
	margin: 50px auto;
	width: 80%;
}
input[type='submit'] {
	display: block;
	margin: 20px auto;
	padding: 5px;
	width: 50%;
}
textarea {
	height: 100px;
	width: 100%;
}
td {
	border-bottom: 1px solid #333;
}
tr:first-child td {
	border-top: 1px solid #333;
}
.c2 {
	width: 160px;
}
.c3 {
	width: 70px;
}
</style>
<body>

<form method="post">
<table>
<?php foreach($DATA as $d): ?>
<tr>
	<td class="c1"><?php echo br2tag( h($d['text']) ); ?></td>
	<td class="c2"><?php eh($d['date']); ?></td>
	<td class="c3"><label><input type="checkbox" name="id[]" value="<?php eh($d['id']); ?>">削除</label></td>
</tr>
<?php endforeach; ?>
</table>
<?php if( count($DATA) ): ?>
<input type="submit" value="メモを削除">
<?php endif; ?>
</form>

<form method="post">
<textarea name="text"></textarea>
<input type="submit" value="記録">
</form>

</body>
</html>