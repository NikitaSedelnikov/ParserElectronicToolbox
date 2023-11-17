# ParserElectronicToolbox
Парсер 100-с-лишним-тысяч товаров, который по времени работы занимает целую вечность

Команда для запуска: <b> php artisan parse </b>

В случае, если необходимо пропарсить несколько страниц с товарами (1 страница примерно 20 товаров) - <b>php artisan parse <число страниц> </b>
# Принцип работы:
Это мой первый опыт работы с парсингом динамических данных (Ajax), а так же первый опыт работы с multi_curl'ом.
Разумеется multi_curl нужен для оптимизации работы. Почему во второй функции так же нет multi_curl'a? Неизвестно по какой причине,
но последовательность товаров cUrl'ов отличалась друг от друга. В итоге в один товар попадали характеристики и крупные картинки из любого другого товара.
Потому, от такой идеи пришлось отказаться и, благодаря multi_cUrl работа скрипта стала пусть не в два, но в полтора раза быстрее.

В процессе изучения сайта были выявлены xhr запросы типа GET, которые отвечали за прогруженную страницу товаров.
Именно оттуда половину данных можно достать через cUrl запрос. 
Касательно крупных картинок, полного описания и характеристик - другой запрос, уже метода POST,
который запускается в каждом товаре отдельно.
Выходит так, что мне пришлось циклом загружать страницу из +- 20 товаров, брать у них как минимум URL и ID,
переходить по ссылкам и отправлять POST запрос, вытягивая из него картинки и описание.

Конечно, описание, название, SKU и т.д. можно было бы достать и просто из страницы HTML, иначе зачем был придуман DOM.
Я даже интегрировал DiDom, поскольку он оптимизированнее встроенного DOMDocument. Однако даже это будет в разы медленнее.

В общем-то, просчет товаров по ссылкам - еще более менее быстро, но вот процесс скачивания картинок по-очереди...

Итог: главная проблема этого проекта - его оптимизация!
