<?php
$auth_key = md5("4869873"."_".intval($_REQUEST['viewer_id'])."_"."Abl74nBHXWu5hq57mAUg");
$user_key = preg_replace("/[^a-f0-9]/i", "", $_REQUEST['auth_key']);
$year_key = dechex(intval(substr(preg_replace("/[^0-9]/", "", $_REQUEST['auth_key']), 0, 8)));

if ($auth_key !== $user_key) {

	// Правдивость данных
	if (!isset($_REQUEST['auth_key'])) {

		echo "Простите, но я буду работать только <a href=\"https://vk.com/app4869873\">со странички приложения</a>";
		exit;

	} else {

		die("Доступ ограничен");

	}

}

if ($_POST) {

	$_POST['viewer_id'] = intval(preg_replace("/[^0-9]+/", "", $_POST['viewer_id']));

	if (isset($_POST['method']) && $_POST['method'] == "takekey") {

		if ($_POST['viewer_id'] < 1) {
	
			echo json_encode(array("error" => array("message" => "Кажется viewer_id это не число")));
	
		} else {
	
			try {
			
				// Открыть базу
				$db = new PDO('sqlite:keys.sqlite');
			
				// Вынуть ключ, которые не принадлежит никому или же тому юзеру, который запустил приложение
				$result = $db->query('SELECT * FROM keys WHERE for_user IS NULL OR for_user = '.$_POST['viewer_id'].' LIMIT 1');
			
				if ($result === false) {
		
					echo json_encode(array("error" => array("message" => "Ой, база данных отсутствует или повреждена")));
	
				} else {
		
					foreach($result as $row) {
	
						if ($row['for_user'] == NULL) {
	
							$update_query = $db->exec('UPDATE keys SET for_user = '.$_POST['viewer_id'].' WHERE key = "'.$row['key'].'"');
		
							if ($update_query === false) {
		
								echo json_encode(array("error" => array("message" => "Ой, не могу записать вас в базу")));
		
							} else {
	
								echo json_encode(array("response" => array("key" => $row['key'])));
		
							}
	
						} else {
	
							echo json_encode(array("error" => array("message" => "Наебать меня решил?", "key" => $row['key'])));
	
						}
					}
		
				}
		
				// close the database connection
				$db = NULL;
		
			} catch(PDOException $e) {
		
				echo json_encode(array("error" => array("message" => "Ошибка базы данных : ".$e->getMessage())));
		
			}
	
		}

	} else if (isset($_POST['method']) && $_POST['method'] == "auth") {

		$_POST['user_id'] = intval(preg_replace("/[^0-9]+/", "", $_POST['user_id']));
		$_POST['user_key'] = preg_replace("/[^0-9a-z]+/i", "", $_POST['user_key']);

		$keyChecked = false;

		if ($_POST['user_id'] > 0) {
	
			// Получил ли юзер ключ?
			try {
				
				// Открыть базу
				$db = new PDO('sqlite:keys.sqlite');
				
				// Вынуть ключ, которые не принадлежит никому или же тому юзеру, который запустил приложение
				$result = $db->query('SELECT * FROM keys WHERE for_user = '.$_POST['user_id'].' AND key = "'.$_POST['user_key'].'" LIMIT 1');
		
				foreach($result as $row) {
					$keyChecked = true;
				}
			
				// close the database connection
				$db = NULL;

			} catch(PDOException $e) {
			
				//
			
			}

			if ($keyChecked == true) {

				echo json_encode(array("response" => "Угадал"));

			} else {

				echo json_encode(array("error" => array("message" => "Введённый код неверный")));

			}

		} else {

			echo json_encode(array("error" => array("message" => "ID юзера ошибочный")));

		}

	} else {

		echo json_encode(array("error" => array("message" => "Не передан метод")));

	}

} else {

	$gotAlready = false;

	// Получил ли юзер ключ?
	try {
		
		// Открыть базу
		$db = new PDO('sqlite:keys.sqlite');
		
		// Вынуть ключ, которые не принадлежит никому или же тому юзеру, который запустил приложение
		$result = $db->query('SELECT * FROM keys WHERE for_user = '.$_REQUEST['viewer_id'].' LIMIT 1');

		foreach($result as $row) {
			$gotAlready = true;
		}
	
		// close the database connection
		$db = NULL;
	
	} catch(PDOException $e) {

		//
	
	}

	if ($gotAlready == true) {
		setcookie("key", $row['key'], time()+60*60*24*30);
		$_COOKIE['key'] = $row['key'];
	}

?>
<!DOCTYPE HTML>
<html lang="ru-RU">
<head>
<title>Типа бот</title>

<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<meta name="robots" content="noindex,nofollow" />

<link rel="stylesheet" type="text/css" href="//yastatic.net/bootstrap/3.3.1/css/bootstrap.min.css" media="all" />
<link rel="stylesheet" type="text/css" href="style.css" media="all" />

<script type="text/javascript" src="//yastatic.net/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript" src="//yastatic.net/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="//yastatic.net/jquery-ui/1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="//vk.com/js/api/xd_connection.js?2"></script>

<script type="text/javascript">
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

// Инициализируется ВКонтакте
VK.init(function() { 

  // Переменные загрузки приложения
  var parts = document.location.search.substr(1).split("&");
  VK.Vars = {};
  for (i=0; i<parts.length; i++) {
      var curr = parts[i].split('=');
      VK.Vars[curr[0]] = curr[1];
  }

  // Подсказываем юзеру его ID
  $("#quick_id_placeholder").html("id"+VK.Vars['viewer_id']);

}, function() { 

    alert("Ой ой ой! Не могу подключиться к ВКонтакте");

}, "5.29");

$(document).ready(function(){

  // Клик по ссылке получения кода
  $("p[class*='YearForeign']").on("click", function(){

<?php
if (isset($_COOKIE['key']) || $gotAlready == true) {
echo '    alert("Пёс, я что не ясно выразился?\nЗапомни свой священный код: '.$_COOKIE['key'].'")';
} else {
?>
    // Обождите
    var yfh = $("p[class*='YearForeign']").html();
    $("p[class*='YearForeign']").html("Подождите ...");	

    // Запрос
    $.ajax({
      url: "index.php",
      type: "POST",
      data: {"viewer_id":VK.Vars['viewer_id'], "auth_key":VK.Vars['auth_key']},
      dataType: "json",
      success: function(data){

        if (data.response) {

          if (data.response.key) {

            $("p[class*='YearForeign']").html("Ваш чудо код - "+data.response.key).off("click");
            setCookie("key", data.response.key, 30);

          } else {

            $("p[class*='YearForeign']").html(yfh);
            alert("Ответ сервера получен, но ключа нету :(");

          }

        } else if (data.error) {

          alert(data.error.message);

          if (data.error.key) {

            setCookie("key", data.error.key, 30);
            $("p[class*='YearForeign']").html("Ещё раз для тех кто в танке, вот код - "+data.error.key).off("click");

          } else {

            $("p[class*='YearForeign']").html(yfh);

          }

        } else {

          alert("Непридвиденная ошибка. Не могу получить код")
          $("p[class*='YearForeign']").html(yfh);

        }

      },
      error: function() {

        alert("Не могу получить код, ошибка запроса.");
        $("p[class*='YearForeign']").html(yfh);

      }
    });
<?php } ?>
  });

});
</script>
</head>
<body>

<div class="container" style="padding-right: 0px; padding-left: 0px">

	<div class="jumbotron" style="padding: 2px 15px; background-color: #45668E; border-radius: 0px; margin-bottom: 14px">
		<p class="text-left" style="margin: 0px; color: #FFFFFF; font-weight: bold">Оффлайн-бот</p>
	</div>

	<div class="row" style="margin: 0px">
		<div class="col-xs-12" style="margin: 4px 10px">
			<p class="text-left"><strong>Оффлайн-бот</strong> – приложение для автоматического заработка баллов<br />в сервисах раскрутки: <strong>Olike, VKmix, TurboLiker </strong>в режиме <strong>Оффлайн 24/7.</strong></p>
		</div>
	</div>

	<div class="row" style="margin: 0px">
		<div class="col-xs-12" style="margin: 4px 10px">
			<p style="color: #45668E; border-bottom: 1px solid #DAE1E8; font-weight: bold">Моментальная регистрация</p>
		</div>
	</div>

	<div class="row" style="margin: 0px">
		<div class="col-xs-4 col-xs-offset-4">
			<form method="post" name="id" id="quick_id_form" action="index.php" autocomplete="off">
				<div class="row"><div class="col-xs-12"><p class="text-left" style="color: #45668E; font-weight: bold">Ваш id</p></div></div>
				<div class="row" style="margin-bottom: 10px"><div class="col-xs-12" style="position: relative">
					<div style="position: absolute; top: 9px; left: 28px"><span id="quick_id_placeholder" style="color: #AAAAAA"></span></div>
					<input type="text" name="id" id="quick_id" class="InputPretty" value="" tabindex="1" />
				</div></div>
				<div class="row"><div class="col-xs-12"><p class="text-left" style="color: #45668E; font-weight: bold">Код доступа</p></div></div>
				<div class="row" style="margin-bottom: 10px"><div class="col-xs-12" style="position: relative">
					<div style="position: absolute; top: 9px; left: 28px"><span id="quick_token_placeholder" style="color: #AAAAAA">Явки, пароли</span></div>
					<input type="password" name="id" id="quick_token" class="InputPretty" value="" tabindex="1" />
				</div></div>
				<div class="row"><div class="col-xs-12"><p class="text-center"><input style="width: 100%" id="btn_sub" class="styled-button" value="Попробовать бесплатно" type="submit" /></p></div></div>
			</form>
		</div>
	</div>
	<div class="row"><div class="col-xs-12"><p class="text-center YearForeign"><?php if (isset($_COOKIE['key']) || $gotAlready == true) { echo "Ты уже получил свой код, пёс!"; } else { echo "Получить код доступа"; } ?></p></div></div>


	<div class="row" style="margin: 0px">
		<div class="col-xs-12" style="margin: 4px 10px">
			<p style="color: #45668E; border-bottom: 1px solid #DAE1E8; font-weight: bold">Ваши преимущества с Оффлайн-ботом</p>
			<ul>
				<li><span>Минимальный риск блокировки.</span></li>
				<li><span>Выполнение самых дорогих заданий.</span></li>
				<li><span>От 10 000 баллов на каждом сервисе ежедневно.</span></li>
				<li><span>Экономия времени - все делается автоматически.</span></li>
				<li><span>Запустили бот - можете выключить компьютер и идти по бабам.</span></li>
				<li><span>Два режима работы: бесплатный - 14ч из 24ч и платный - 24ч из 24ч.</span></li>
				<li><span>Поиск одинаковых заданий на сервисах - "одним выстрелом трех зайцев!"</span></li>
				<li><span>Поддержка всех аккаунтов доступных в сервисах: Olike, VKmix, TurboLiker.</span></li>
				<li><span>Автоматическая смена аккаунта в случае блокировки и уведомление вас об этом.</span></li>
				<li><span><strong>New! </strong>Мультиаккаунтный режим - зарабатывайте тонны баллов каждый день!</span></li>
			</ul>
		</div>
	</div>

</div>

<script type="text/javascript">
// Отправка формы
$("#quick_id_form").on("submit", function(e){

	e.preventDefault();

	// Подсветка полей и отправка формы, если всё в порядке
	if ($("#quick_id").val().length == 0) {
		$("#quick_id").animate({ backgroundColor: "#FAEAEA", color: "#FFFFFF" }, 500, function(){ $(this).animate({ backgroundColor: "#FFFFFF", color: "#555" }, 500) } );
	} else if ($("#quick_token").val().length == 0) {
		$("#quick_token").animate({ backgroundColor: "#FAEAEA", color: "#FFFFFF" }, 500, function(){ $(this).animate({ backgroundColor: "#FFFFFF", color: "#555" }, 500) } );
	} else {

		var uid = /([0-9]+)/.exec( $("#quick_id").val() );

		if (uid != null) {

			// Запрос
			$.ajax({
				url: "index.php",
				type: "POST",
				data: {"viewer_id":VK.Vars['viewer_id'], "auth_key":VK.Vars['auth_key'], "method":"auth", "user_id":uid[0], "user_key":$("#quick_token").val() },
				dataType: "json",
				success: function(data){
					if (data.response) {
						alert(data.response);
						self.location.href = "https://www.youtube.com/embed/gGRFS4-lijU?autoplay=true";
					} else if (data.error) {
						if (data.error.message) {
							alert(data.error.message);
						} else {
							alert("Произошла ошибка");
						}
					} else {
						alert("Ой. Что-то странное случилось");
					}
				},
				error: function() {
					alert("Ошибка запроса");
				}
			});

		} else {

			alert("Вводить следует идентификатор вида \"id123456789\"");

		}

	}

	return false;

});

// Эмуляция затухания подсказок
$("#quick_id_placeholder, #quick_token_placeholder").on("mousedown", function(){
	var ti = $(this).prop("id");
	var ri = /([a-z\_]+)_placeholder/.exec(ti);
	if (ri != null) {
		$("#"+ri[1]).focus();
	}
	return false;
});

$("#quick_id, #quick_token").on("focus blur keyup change", function(e){

	var op = $("#"+$(this).prop("id")+"_placeholder").css("opacity");
	var ln = $(this).val().length;
	var ti = $(this).prop("id");

	switch (true) {

		case (ln == 0):
			if (e.type == "blur") {

				if (op == 0.5 || op == 0) {
					$("#"+ti+"_placeholder").show().animate({"opacity":1.0}, 500);
				}

			} else {

				if (op == 0 || op == 1) {
					$("#"+ti+"_placeholder").show().animate({"opacity":0.5}, 500);
				}

			}
		break;

		case (ln > 0):

			if (op == 1 || op == 0.5) {
				$("#"+ti+"_placeholder").animate({"opacity":0.0}, 500, function(){
					$(this).hide();
				});
			}

		break;

	}

});

$("#quick_token").on("keydown keyup blur", function(){
	if ($(this).val().length > 0) {
		$("p[class*='YearForeign']").hide();
	} else {
		$("p[class*='YearForeign']").show();
	}
});

// Документ готов
$(document).ready(function(){

	// Выделение первого поля
	$("#quick_id").focus();

});

</script>

</body>
</html>
<? } ?>