<?php 
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db = "hope-for-brain";
 
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$result_table = $mysqli->query("SELECT * FROM priselist");

if ($result_table === FALSE) {
	//print ("Создаем таблицу");
	f_create_table($mysqli);
} else if (mysqli_num_rows($result_table) == 0) {
	//print ("Наполняем таблицу");
	f_parsing_csv($mysqli);
} else {
	//print ("Выводим таблицу"); //Печать сообщения
	f_print_table($mysqli);
}

//Создание таблицы
function f_create_table($mysqli){
	$sql="CREATE TABLE priselist (id INT(4) NOT NULL auto_increment primary key, name_goods VARCHAR(100) NOT NULL, coast FLOAT(10,2) NOT NULL, coast_wholesale FLOAT(10,2) NOT NULL, presence_1 INT(10) NOT NULL, presence_2 INT(10) NOT NULL, country VARCHAR(100) NOT NULL)";

	$res = $mysqli->query($sql);

	if ($res == true)
	   print ("Таблица базы данных успешно создана");
	else
	   print ("Запрос не выполнен");
}

//Парсинг
function readCSV($csvFile){
	$file_handle = fopen($csvFile, 'r');
	while (!feof($file_handle) ) {
		$line_of_text[] = fgetcsv($file_handle, 1024);
	}
	fclose($file_handle);
	return $line_of_text;
}

function f_parsing_csv($mysqli) {
	$csvFile = 'priselist.csv';

	$csv = readCSV($csvFile);
	$COUNT_ROW_IMPORT = 0;
	foreach ($csv as $key => $flat) {
		// Пропускаем первую строку с заголовком
		if($key == 0) continue;
		// Пропускаем пустые строки
		if(!is_array($flat) || $flat == '') continue;

		$name_goods = $flat[0];
		$coast = str_replace(',', '.', $flat[1]);
		$coast_wholesale = str_replace(',', '.', $flat[2]);
		$presence_1 = intval($flat[3]);
		$presence_2 = intval($flat[4]);
		$country = $flat[5];

		$sql = "INSERT into priselist (name_goods,coast,coast_wholesale,presence_1,presence_2,country) values ('".$name_goods."','".$coast."','".$coast_wholesale."','".$presence_1."','".$presence_2."','".$country."')";
		$res = $mysqli->query($sql);
		
		if(!isset($res))
		{
			echo "Invalid File:Please Upload CSV File.";

		} else {
			$COUNT_ROW_IMPORT++;
		}	
	}
	if($COUNT_ROW_IMPORT != 0)
	{
		echo "CSV File has been successfully Imported. Count row: " . $COUNT_ROW_IMPORT;    
	} 
}

function f_print_table($mysqli){
	$res = $mysqli->query("SELECT * FROM priselist");
	$res1 = $mysqli->query("SELECT * FROM priselist");
	//$row = $res->fetch_assoc();
	$p1 = array();
	$p2 = array();
	$cres = array();
	//$cres = 0;
	$csres = array();
	$cres1 = array();
	//$cres = 0;
	$csres1 = array();
	//if (mysqli_num_rows($res) > 0) {
		echo "<table>
	        <thead>
	         <tr>
				<th>Номер</th>
				<th>Наименование товара</th>
				<th>Стоимость, руб</th>
				<th>Стоимость опт, руб</th>
				<th>Наличие на складе 1, шт</th>
				<th>Наличие на складе 2, шт</th>
				<th>Страна производства</th>
				<th>Примечание</th>
	         </tr>
	        </thead>
	     	<tbody>";

	     while($row1 = $res1->fetch_assoc()) {
	     	array_push($cres1, floatval($row1['coast']));
	     	array_push($csres1, $row1['coast_wholesale']);
	     }
	     	$maxcres = max($cres1);
	     	$mincsres = min($csres1);

	     while($row = $res->fetch_assoc()) {
	     	array_push($p1, intval($row['presence_1']));
			array_push($p2, intval($row['presence_2']));
			array_push($cres, floatval($row['coast']));
			array_push($csres, $row['coast_wholesale']);
			$text = "";
			if ($row['presence_1'] < 20 || $row['presence_2'] < 20){ 
				$text = "Осталось мало!! Срочно докупите!!!";
			}
			if ($row['coast'] == $maxcres ){
		        echo "<tr style='background:red;'>
		         		<td>" . $row['id']."</td>
		                <td>" . $row['name_goods']."</td>
		                <td>" . $row['coast']."</td>
		                <td>" . $row['coast_wholesale']."</td>
		                <td>" . $row['presence_1']."</td>
		                <td>" . $row['presence_2']."</td>
		                <td>" . $row['country']."</td>
		                <td>" . $text . "</td>
		           </tr>";        
			}
			elseif ($row['coast_wholesale'] == $mincsres) {
				echo "<tr style='background:green;'>
	         		<td>" . $row['id']."</td>
	                <td>" . $row['name_goods']."</td>
	                <td>" . $row['coast']."</td>
	                <td>" . $row['coast_wholesale']."</td>
	                <td>" . $row['presence_1']."</td>
	                <td>" . $row['presence_2']."</td>
	                <td>" . $row['country']."</td>
	                <td>" . $text . "</td>
	              </tr>"; 
			}
			else{
				echo "<tr>
	         		<td>" . $row['id']."</td>
	                <td>" . $row['name_goods']."</td>
	                <td>" . $row['coast']."</td>
	                <td>" . $row['coast_wholesale']."</td>
	                <td>" . $row['presence_1']."</td>
	                <td>" . $row['presence_2']."</td>
	                <td>" . $row['country']."</td>
	                <td>" . $text . "</td>
	              </tr>";   
			}
	     }
	     echo "</tbody></table>";

	     $sump1 = array_sum($p1);
	     $sump2 = array_sum($p2);
	     $sump12 = (array_sum($p1) + array_sum($p2));
	     $sumpcres = round((array_sum($cres) / count($cres)),2);
	     $sumcsres = round((array_sum($csres) / count($csres)), 2);

	     echo "Общее количество товаров на Складе1:" . $sump1 . "<br>";
	     echo "Общее количество товаров на Складе2:" . $sump2 . "<br>";
	     echo "Общее количество товаров на Складах:" . $sump12 . "<br>";
	     echo "Средняя стоимость розничной цены товара:" . $sumpcres. "<br>";
	     echo "Средняя стоимость оптовой цены товара:" . $sumpcsres . "<br>";
}
exit();
?>