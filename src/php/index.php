<?
mysql_connect("db","root","root");
mysql_select_db("sqli");
$user_id = $_GET['id'];
$sql = mysql_query("SELECT username, nom, prenom, email FROM users WHERE user_id = $user_id") or die(mysql_error());
if(mysql_num_rows($sql) > 0)
{
$data = mysql_fetch_object($sql);
echo "
<fieldset>
<legend>Profile de ".$data->username."</legend>
<p>Nom d'utilisateur : ".$data->username."</p>
<p>Nom et prénom : ".$data->nom." " .$data->prenom ."</p>
<p>Adresse email : ".$data->email."</p>
</fieldset>";
}
?>