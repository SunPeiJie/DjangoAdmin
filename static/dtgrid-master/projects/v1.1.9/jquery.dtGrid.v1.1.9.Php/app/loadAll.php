<?php

//	模拟用户数据
	$userList = array();
	for($i=0; $i<186; $i++){
		$user = new stdClass();
		$user->user_id = "user_".$i;
		$user->user_code = "user_".$i;
		$user->user_name = "模拟用户".mt_rand(10000, 99999)."号";
		$user->sex = mt_rand(1, 2);
		$user->salary = mt_rand(5000, 12000);
		$user->degree = mt_rand(1, 8);
		$user->time = "不作处理";
		$user->time_stamp_s = mt_rand(315504000, (315504000+1096588800));
		$user->time_stamp_ms = mt_rand(315504000, (315504000+1096588800))*1000;
		$user->string_date = date("Y-m-d", mt_rand(315504000, (315504000+1096588800)));
		$user->string_time = date("Y-m-d H:i:s", mt_rand(315504000, (315504000+1096588800)));
		array_push($userList, $user);
	}
	echo json_encode($userList);

?>