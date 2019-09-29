<?php
	
	require_once "/var/www/html/gates/team-one/utils.php";
	
	/*
	
	Events:
	id
	dt
	event_type
	payload
	state
	
	---
	
	Subscribes:
	id
	event_type
	handler_id
	
	---
	
	Handlers:
	id
	about
	cmd
	
	*/
	
		
	$events = [];
	
	$e1 = (object) array('id' => 1, 'dt' => 1, 'type' => 'ORDER_CREATED', 'payload' => "{}", 'state' => 0);
	$e2 = (object) array('id' => 2, 'dt' => 2, 'type' => 'NOTIFY', 'payload' => "{}", 'state' => 0);
	$e3 = (object) array('id' => 3, 'dt' => 3, 'type' => 'API', 'payload' => "{}", 'state' => 0);
	$e4 = (object) array('id' => 4, 'dt' => 4, 'type' => 'API', 'payload' => "{}", 'state' => 1);
	$e5 = (object) array('id' => 5, 'dt' => 5, 'type' => 'ORDER_COMPLETED', 'payload' => "{}", 'state' => 0);
	
	$events[$e1->id] = $e1;
	$events[$e2->id] = $e2;
	$events[$e3->id] = $e3;
	$events[$e4->id] = $e4;
	$events[$e5->id] = $e5;
	
	$subscribes = [];
	
	$s1 = (object) array('id' => 1, 'event_type' => 'ORDER_CREATED', 'handler_id' => 1);
	$s2 = (object) array('id' => 2, 'event_type' => 'NOTIFY', 'handler_id' => 2);
	$s3 = (object) array('id' => 3, 'event_type' => 'API', 'handler_id' => 3);
	
	$subscribes[$s1->event_type][] = $s1;
	$subscribes[$s2->event_type][] = $s2;
	$subscribes[$s3->event_type][] = $s3;
	
	$h1 = (object) array('id' => 1, 'about' => 'Обрабатывает новые заказы', 'cmd' => '');
	$h2 = (object) array('id' => 2, 'about' => 'Отправляет уведомления', 'cmd' => '');
	$h3 = (object) array('id' => 3, 'about' => 'Делаем внешние вызовы', 'cmd' => '');
	
	$handlers[$h1->id] = $h1;
	$handlers[$h2->id] = $h2;
	$handlers[$h3->id] = $h3;
	
	//1. Взять новые события
	//2. Пометить события как выполняющиеся
	//3. Взять обработчики, которые подписанны
	//4. Запустить их
	
	utils_log("---");
	utils_log("Запрос новых событий...");
	foreach ($events as $e) 
		if ($e->state == 0) {
			utils_log("Обработка события #".$e->id.", ".$e->type);
			$e->state = 1;
			if (isset($subscribes[$e->type])) {
				utils_log("Есть подписки на событие ".$e->type);
				$ss = $subscribes[$e->type];
				foreach ($ss as $s) 
					if (isset($handlers[$s->handler_id])) {
						$h = $handlers[$s->handler_id];
						utils_log("Запуск обработчика #".$h->id.", ".$h->about.", ".$h->cmd);
						//exec($h->cmd." > /dev/null &"); 
					}
			}
			
		}
	
	
?>