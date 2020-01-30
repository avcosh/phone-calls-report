<?php
if (isset($_POST['submit'])){
 
  $start = htmlspecialchars($_POST['start']);
  $start = trim($start);
  $start = strip_tags($start);
  $end = htmlspecialchars($_POST['end']);
  $end = trim($end);
  $end = strip_tags($end);
 
  require 'Db.php';
  $db = Db::getConnection();
  
  // Первый отчет
  $sql = 'select local_num,sum(case direction when 1 then 1 end) as income,
          sum(case direction when 2 then 1 end) as outcome,
		  sum(case direction when 1 then duration end) as incomesec,
		  sum(case direction when 2 then duration end) as outcomesec 
		  from shop_sip_calls where datetime_mysql 
		  between :start and :end 
		  group by local_num';
  $result = $db->prepare($sql);
  $result->bindParam(':start', $start, PDO::PARAM_STR);
  $result->bindParam(':end', $end, PDO::PARAM_STR);
  $result->setFetchMode(PDO::FETCH_ASSOC);
  $result->execute();
  $i = 0;
        $numList = array();
        while ($row = $result->fetch()) {
            $numList[$i]['local_num'] = $row['local_num'];
            $numList[$i]['income'] = $row['income'];
            $numList[$i]['outcome'] = $row['outcome'];
            $numList[$i]['incomesec'] = $row['incomesec'];
			$numList[$i]['outcomsec'] = $row['outcomesec'];
            
            $i++;
        }
		
	// Второй отчет
	 $sql2 = 'with phone_cnt as
			(select local_num,phone, count(*) cnt
			from shop_sip_calls
			where datetime_mysql between :start and :end
			group by local_num,phone)
			, max_per as
			(select local_num, max(cnt) mcnt from phone_cnt group by local_num)

			select phone_cnt.local_num,phone_cnt.phone, max_per.mcnt as quant  
			from max_per join phone_cnt on max_per.local_num = phone_cnt.local_num and max_per.mcnt = phone_cnt.cnt';
			
  $result2 = $db->prepare($sql2);
  $result2->bindParam(':start', $start, PDO::PARAM_STR);
  $result2->bindParam(':end', $end, PDO::PARAM_STR);
  $result2->setFetchMode(PDO::FETCH_ASSOC);
  $result2->execute();
  $i = 0;
        $numList2 = array();
        while ($row2 = $result2->fetch()) {
            $numList2[$i]['local_num'] = $row2['local_num'];
            $numList2[$i]['phone'] = $row2['phone'];
            $numList2[$i]['quant'] = $row2['quant'];
           
            $i++;
        }
		
	// Третий отчет
	  $sql3 = 'with phone_time as
				(select local_num,phone, sum(duration) cnt
				from shop_sip_calls
				where datetime_mysql between :start and :end
				group by local_num,phone)
				, max_per as
				(select local_num, max(cnt) mcnt from phone_time group by local_num)

				select phone_time.local_num,phone_time.phone, max_per.mcnt as leng  
				from max_per join phone_time on max_per.local_num = phone_time.local_num and max_per.mcnt = phone_time.cnt';
			
			  $result3 = $db->prepare($sql3);
			  $result3->bindParam(':start', $start, PDO::PARAM_STR);
			  $result3->bindParam(':end', $end, PDO::PARAM_STR);
			  $result3->setFetchMode(PDO::FETCH_ASSOC);
			  $result3->execute();
			  $i = 0;
					$numList3 = array();
					while ($row3 = $result3->fetch()) {
						$numList3[$i]['local_num'] = $row3['local_num'];
						$numList3[$i]['phone'] = $row3['phone'];
						$numList3[$i]['leng'] = $row3['leng'];
					   
						$i++;
					}
	
    }  	
?>	
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>shop_sip_calls</title>
  </head>
  <body>
  <div class="container">
    <h1> Отчеты за выбранный период </h1>
	<hr>
	<h2>Отчет №1</h2>
	
	<table class="table">
    <thead class="thead-dark">
    <tr>
      <th scope="col">Local_num</th>
      <th scope="col">Входящих(шт)</th>
      <th scope="col">Исходящих(шт)</th>
      <th scope="col">Входящих(сек)</th>
	  <th scope="col">Исходящих(сек)</th>
    </tr>
    </thead>
    <tbody>
        <?php foreach($numList as $item):?>
    <tr>
      
      <td><?= $item['local_num']?></td>
      <td><?= $item['income']?></td>
	  <td><?= $item['outcome']?></td>
      <td><?= $item['incomesec']?></td>
	  <td><?= $item['outcomsec']?></td>
      
      
    </tr>
        <?php endforeach ?>
    </tbody>
    </table>
	
	<hr>
	
    <h2>Отчет №2</h2>
    
    <table class="table">
    <thead class="thead-dark">
    <tr>
      <th scope="col">Local_num</th>
      <th scope="col">Phone</th>
      <th scope="col">Кол-во звонков</th>
    </tr>
    </thead>
    <tbody>
        <?php foreach($numList2 as $item2):?>
    <tr>
      
      <td><?= $item2['local_num']?></td>
      <td><?= $item2['phone']?></td>
	  <td><?= $item2['quant']?></td>
     
    </tr>
        <?php endforeach ?>
    </tbody>
    </table>
	
	<hr>
	
	<h2>Отчет №3</h2>
    
    <table class="table">
    <thead class="thead-dark">
    <tr>
      <th scope="col">Local_num</th>
      <th scope="col">Phone</th>
      <th scope="col">Длина разговоров в сек</th>
    </tr>
    </thead>
    <tbody>
        <?php foreach($numList3 as $item3):?>
    <tr>
      
      <td><?= $item3['local_num']?></td>
      <td><?= $item3['phone']?></td>
	  <td><?= $item3['leng']?></td>
     
    </tr>
        <?php endforeach ?>
    </tbody>
    </table>
	
	<a href = "index.php">На главную</a>
    <hr>
    </div>   
    </body>



 