// let selector = ".filters .filter-category .dropdown-content > div";
// let sel;
// let parent_categories_length = 20;
// let sub_categories_length = [14, 6, 3, 14, 10, 7, 8, 12, 3, 25, 12, 33, 21, 6, 3, 17, 3, 2, 9, 8];
let krupy_boby = {
	'1': 'горох',
	'2': 'гречневая каша',
	'3': 'маш',
	'4': 'пшено',
	'5': 'фасоль',
};
let mineralka = {
	'1': 'вода родниковая',
};
let ukrop = {
	'1': 'укроп',
};
let bahcha = {
	'1': 'арбуз',
	'2': 'дыня',
};
let ris = {
	'1': 'рис белый',
	'2': 'рис бурый',
	'3': 'рис красный',
	'4': 'рис пропаренный',
	'5': 'рис черный',
};

let fruit_garden = {
	'1': 'апельсин',
	'2': 'арбуз',
	'3': 'виноград',
	'4': 'вода родниковая',
	'5': 'груша',
	'6': 'дыня',
	'7': 'клюква',
	'8': 'укроп',
	'9': 'финик',
};

let lihodeev_supplier = {
	'1': 'горох',
	'2': 'гречневая каша',
	'3': 'крупа пшеничная',
	'4': 'манная каша',
	'5': 'маш',
	'6': 'нектарины',
	'7': 'персики',
	'8': 'пшено',
	'9': 'рис белый',
	'10': 'рис бурый',
	'11': 'рис красный',
	'12': 'рис пропаренный',
	'13': 'рис черный',
	'14': 'свекла',
	'15': 'фасоль',
	'16': 'яблоки голден',
	'17': 'яблоки гренни смит',
	'18': 'яблоки сезонные',
};

let price_filter = {
	'1': ['апельсин', '800 RUB'],
	'2': ['арбуз', '200 RUB'],
	'3': ['виноград', '1500 RUB'],
	'4': ['вода родниковая', '300 RUB'],
	'5': ['гречневая каша', '90 RUB'],
	'6': ['груша', '600 RUB'],
	'7': ['дыня', '100 RUB'],
	'8': ['клюква', '1111 RUB'],
	'9': ['крупа пшеничная', '70 RUB'],
	'10': ['маш', '105 RUB'],
	'11': ['нектарины', '140 RUB'],
	'12': ['персики', '120 RUB'],
	'13': ['пшено', '50 RUB'],
	'14': ['рис бурый', '100 RUB'],
	'15': ['рис красный', '100 RUB'],
	'16': ['рис пропаренный', '50 RUB'],
	'17': ['рис черный', '150 RUB'],
	'18': ['укроп', '111 RUB'],
	'19': ['фасоль', '100 RUB'],
	'20': ['яблоки голден', '90 RUB'],
	'21': ['яблоки гренни смит', '80 RUB'],
	'22': ['яблоки сезонные', '70 RUB'],
};

let filter_sort_a_ya =
	{
		'1': ["апельсин", "Ко Фруктовый сад", "800 RUB"],
		'2': ["арбуз", "Ко Фруктовый сад", "200 RUB"],
		'3': ["виноград", "Ко Фруктовый сад", "1500 RUB"],
		'4': ["вода родниковая", "Ко Фруктовый сад", "300 RUB"],
		'5': ["горох", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
		'6': ["гречневая каша", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
		'7': ["груша", "Ко Фруктовый сад", "600 RUB"],
		'8': ["дыня", "Ко Фруктовый сад", "100 RUB"],
		'9': ["клюква", "Ко Фруктовый сад", "1111 RUB"],
		'10': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
		'11': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB"],
		'12': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB"],
		'13': ["нектарины", "ОАО \"Лиходеев-Поставщик\"", "140 RUB"],
		'14': ["персики", "ОАО \"Лиходеев-Поставщик\"", "120 RUB"],
		'15': ["пшено", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
		'16': ["рис белый", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
		'17': ["рис бурый", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
		'18': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
		'19': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
		'20': ["рис черный", "ОАО \"Лиходеев-Поставщик\"", "150 RUB"],
		'21': ["свекла", "ОАО \"Лиходеев-Поставщик\"", "25 RUB"],
		'22': ["укроп", "Ко Фруктовый сад", "111 RUB"],
		'23': ["фасоль", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
		'24': ["финик", "Ко Фруктовый сад", "3009 RUB"],
		'25': ["яблоки голден", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
		'26': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB"],
		'27': ["яблоки сезонные", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"]
	};

let filter_sort_ya_a = {
	'1': ["яблоки сезонные", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'2': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB"],
	'3': ["яблоки голден", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'4': ["финик", "Ко Фруктовый сад", "3009 RUB"],
	'5': ["фасоль", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'6': ["укроп", "Ко Фруктовый сад", "111 RUB"],
	'7': ["свекла", "ОАО \"Лиходеев-Поставщик\"", "25 RUB"],
	'8': ["рис черный", "ОАО \"Лиходеев-Поставщик\"", "150 RUB"],
	'9': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'10': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'11': ["рис бурый", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'12': ["рис белый", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'13': ["пшено", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'14': ["персики", "ОАО \"Лиходеев-Поставщик\"", "120 RUB"],
	'15': ["нектарины", "ОАО \"Лиходеев-Поставщик\"", "140 RUB"],
	'16': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB"],
	'17': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB"],
	'18': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'19': ["клюква", "Ко Фруктовый сад", "1111 RUB"],
	'20': ["дыня", "Ко Фруктовый сад", "100 RUB"],
	'21': ["груша", "Ко Фруктовый сад", "600 RUB"],
	'22': ["гречневая каша", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'23': ["горох", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'24': ["вода родниковая", "Ко Фруктовый сад", "300 RUB"],
	'25': ["виноград", "Ко Фруктовый сад", "1500 RUB"],
	'26': ["арбуз", "Ко Фруктовый сад", "200 RUB"],
	'27': ["апельсин", "Ко Фруктовый сад", "800 RUB"]
};

let filter_sort_price_up = {
	'1': ["свекла", "ОАО \"Лиходеев-Поставщик\"", "25 RUB"],
	'2': ["рис белый", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'3': ["горох", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'4': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB"],
	'5': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'6': ["пшено", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'7': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'8': ["яблоки сезонные", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'9': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB"],
	'10': ["гречневая каша", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'11': ["яблоки голден", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'12': ["дыня", "Ко Фруктовый сад", "100 RUB"],
	'13': ["рис бурый", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'14': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'15': ["фасоль", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'16': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB"],
	'17': ["укроп", "Ко Фруктовый сад", "111 RUB"],
	'18': ["персики", "ОАО \"Лиходеев-Поставщик\"", "120 RUB"],
	'19': ["нектарины", "ОАО \"Лиходеев-Поставщик\"", "140 RUB"],
	'20': ["рис черный", "ОАО \"Лиходеев-Поставщик\"", "150 RUB"],
	'21': ["арбуз", "Ко Фруктовый сад", "200 RUB"],
	'22': ["вода родниковая", "Ко Фруктовый сад", "300 RUB"],
	'23': ["груша", "Ко Фруктовый сад", "600 RUB"],
	'24': ["апельсин", "Ко Фруктовый сад", "800 RUB"],
	'25': ["клюква", "Ко Фруктовый сад", "1111 RUB"],
	'26': ["виноград", "Ко Фруктовый сад", "1500 RUB"],
	'27': ["финик", "Ко Фруктовый сад", "3009 RUB"],
};

let filter_sort_price_down = {

	'1': ["финик", "Ко Фруктовый сад", "3009 RUB"],
	'2': ["виноград", "Ко Фруктовый сад", "1500 RUB"],
	'3': ["клюква", "Ко Фруктовый сад", "1111 RUB"],
	'4': ["апельсин", "Ко Фруктовый сад", "800 RUB"],
	'5': ["груша", "Ко Фруктовый сад", "600 RUB"],
	'6': ["вода родниковая", "Ко Фруктовый сад", "300 RUB"],
	'7': ["арбуз", "Ко Фруктовый сад", "200 RUB"],
	'8': ["рис черный", "ОАО \"Лиходеев-Поставщик\"", "150 RUB"],
	'9': ["нектарины", "ОАО \"Лиходеев-Поставщик\"", "140 RUB"],
	'10': ["персики", "ОАО \"Лиходеев-Поставщик\"", "120 RUB"],
	'11': ["укроп", "Ко Фруктовый сад", "111 RUB"],
	'12': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB"],
	'13': ["дыня", "Ко Фруктовый сад", "100 RUB"],
	'14': ["рис бурый", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'15': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'16': ["фасоль", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'17': ["гречневая каша", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'18': ["яблоки голден", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'19': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB"],
	'20': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'21': ["яблоки сезонные", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'22': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'23': ["пшено", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'24': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB"],
	'25': ["рис белый", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'26': ["горох", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'27': ["свекла", "ОАО \"Лиходеев-Поставщик\"", "25 RUB"],
};

let filter_sort_supplier = {
	'1': ["вода родниковая", "Ко Фруктовый сад", "300 RUB"],
	'2': ["клюква", "Ко Фруктовый сад", "1111 RUB"],
	'3': ["укроп", "Ко Фруктовый сад", "111 RUB"],
	'4': ["дыня", "Ко Фруктовый сад", "100 RUB"],
	'5': ["арбуз", "Ко Фруктовый сад", "200 RUB"],
	'6': ["финик", "Ко Фруктовый сад", "3009 RUB"],
	'7': ["груша", "Ко Фруктовый сад", "600 RUB"],
	'8': ["виноград", "Ко Фруктовый сад", "1500 RUB"],
	'9': ["апельсин", "Ко Фруктовый сад", "800 RUB"],
	'10': ["рис бурый", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'11': ["рис белый", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'12': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'13': ["рис черный", "ОАО \"Лиходеев-Поставщик\"", "150 RUB"],
	'14': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'15': ["пшено", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'16': ["гречневая каша", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'17': ["горох", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'18': ["фасоль", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'19': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB"],
	'20': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'21': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB"],
	'22': ["персики", "ОАО \"Лиходеев-Поставщик\"", "120 RUB"],
	'23': ["нектарины", "ОАО \"Лиходеев-Поставщик\"", "140 RUB"],
	'24': ["свекла", "ОАО \"Лиходеев-Поставщик\"", "25 RUB"],
	'25': ["яблоки сезонные", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
	'26': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB"],
	'27': ["яблоки голден", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
};

module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1630...');
		console.log('Filter and sorting tests...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1630...');
		browser.end();
	},

	// 'Test DEV-1630 filter-category': function (browser) {
	//
	//     // let array_length = browser.globals.vars.length_array;
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 5000, function () {
	//         console.log('email input appeared...');
	//     });
	//     browser.waitForElementVisible('#password', 5000, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', 5000, function () {
	//         console.log('drop-down trigger appeared appeared...');
	//     });
	//     browser.click('.dropdown-trigger');
	//     browser.click('.dropdown-content a:nth-child(1)');
	//     // browser.saveScreenshot('screenshotstest1.png');
	//
	//     browser.clearValue('#email', function () {
	//         console.log('Clearing email input!!!');
	//     });
	//     browser.clearValue('#password', function () {
	//         console.log('Clearing password input!!!');
	//     });
	//
	//     browser.setValue('#email', 'mixcart@bk.ru');
	//     browser.setValue('#password', 'max999');
	//     browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru');
	//     browser.expect.element('#password').to.have.value.that.equals('max999');
	//     browser.submitForm('form');
	//
	//     browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').after(3000);
	//
	//     browser.click('.guest-form-fields a:nth-child(1)');
	//
	//     browser.waitForElementVisible('.header-profile-name', 5000, function (result) {
	//         console.log('.header-profile-name appeared...');
	//     });
	//
	//     browser.url('http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//Переходим
	// на страницу заказов });  browser.resizeWindow(1980, 3000);  // browser.pause(3000);
	// browser.waitForElementVisible('.filters .filter-category', 5000);   browser.pause(2000);  function test_exist(b,
	// x1, y1, array) { let x = x1; let y = y1; b.execute(function (x, y) { let menu = document.querySelector('.filters
	// .filter-category .dropdown-content > div:nth-child(' + x + ') div a:nth-child(' + y + ')');
	// menu.setAttribute('id', 'test'); document.getElementById('test').click(); }, [x, y]);  for (let key in array) {
	// b.expect.element('app-product-list table tbody tr:nth-child(' + key + ')
	// td:nth-child(1)').text.to.equal(array[key]).before(5000); }  b.saveScreenshot('screenshotstest1_' + x + '_' + y +
	// '.png'); b.execute(function () { let a = document.getElementById('test'); a.removeAttribute('id'); }, ['','']);
	// b.pause(1000); }  function test_not_exist(b, x1, y1) { let x = x1; let y = y1; b.execute(function (x, y) { let
	// menu = document.querySelector('.filters .filter-category .dropdown-content > div:nth-child(' + x + ') div
	// a:nth-child(' + y + ')'); menu.setAttribute('id', 'test'); document.getElementById('test').click(); }, [x, y]);
	// b.waitForElementVisible('app-product-list table tbody .nothing', 3000); b.expect.element('app-product-list table
	// tbody .nothing').text.to.equal('Продукты не найдены');  b.saveScreenshot('screenshotstest1_' + x + '_' + y +
	// '.png'); b.execute(function () { let a = document.getElementById('test'); a.removeAttribute('id'); }, []);
	// b.pause(1000); }  test_not_exist(browser, 1, 1); test_not_exist(browser, 4, 4); test_not_exist(browser, 6, 6);
	// test_exist(browser, 7, 5, mineralka); browser.elements('css selector', 'app-product-list table tbody tr',
	// function(res) { console.log('row count: '+res.value.length); browser.assert.equal(res.value.length,
	// Object.keys(mineralka).length); }); test_exist(browser, 10, 6, ukrop); browser.elements('css selector',
	// 'app-product-list table tbody tr', function(res) { console.log('row count: '+res.value.length);
	// browser.assert.equal(res.value.length, Object.keys(ukrop).length); }); test_exist(browser, 11, 1, bahcha); browser.elements('css selector', 'app-product-list table tbody tr', function(res) { console.log('row count: '+res.value.length); browser.assert.equal(res.value.length, Object.keys(bahcha).length); }); test_exist(browser, 13, 2, krupy_boby); browser.elements('css selector', 'app-product-list table tbody tr', function(res) { console.log('row count: '+res.value.length); browser.assert.equal(res.value.length, Object.keys(krupy_boby).length); }); test_exist(browser, 13, 11, ris); browser.elements('css selector', 'app-product-list table tbody tr', function(res) { console.log('row count: '+res.value.length); browser.assert.equal(res.value.length, Object.keys(ris).length); });  browser.expect.element("span[class='header-profile-name']").text.to.equal('Ресторан в доме у Грибоедова').before(10000);  browser.pause(2000); browser.click("span[class='header-profile-name']"); browser.click("a[class='button-link header-user-logout']"); browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(10000); //Ждём пока не выйдем  },

	// 'Test DEV-1630 filter-supplier': function (browser) {
	//
	//     // let array_length = browser.globals.vars.length_array;
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 5000, function () {
	//         console.log('email input appeared...');
	//     });
	//     browser.waitForElementVisible('#password', 5000, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', 5000, function () {
	//         console.log('drop-down trigger appeared appeared...');
	//     });
	//     browser.click('.dropdown-trigger');
	//     browser.click('.dropdown-content a:nth-child(1)');
	//     // browser.saveScreenshot('screenshotstest1.png');
	//
	//     // browser.clearValue('#email', function () {
	//     //     console.log('Clearing email input!!!');
	//     // });
	//     // browser.clearValue('#password', function () {
	//     //     console.log('Clearing password input!!!');
	//     // });
	//
	//     browser.setValue('#email', 'mixcart@bk.ru');
	//     browser.setValue('#password', 'max999');
	//     browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru');
	//     browser.expect.element('#password').to.have.value.that.equals('max999');
	//     browser.submitForm('form');
	//
	//     browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').after(5000);
	//
	//     browser.click('.guest-form-fields a:nth-child(1)');
	//
	//     browser.waitForElementVisible('.header-profile-name', 5000, function (result) {
	//         console.log('.header-profile-name appeared...');
	//     });
	//
	//     browser.url('http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//Переходим
	// на страницу заказов });  browser.resizeWindow(1980, 3000);  browser.pause(3000);
	// browser.waitForElementVisible('.filters .filter-supplier', 5000);   //filter-supplier begin  function
	// test_filter_result(b, array, supplierId) { b.execute(function (supplierId) {
	// document.getElementById(supplierId).click(); console.log(supplierId); }, [supplierId]); b.pause(7000); for (key in
	// array) { b.expect.element('app-product-list table tbody tr:nth-child(' + key + ')
	// td:nth-child(1)').text.to.equal(array[key]).before(5000); }  }  function uncheck_filter(b, supplierId) {
	// b.execute(function (supplierId) { document.getElementById(supplierId).click(); }, [supplierId]); b.pause(7000); }
	// //supplier0 test_filter_result(browser, fruit_garden, 'supplier0');  browser.elements('css selector',
	// 'app-product-list table tbody tr', function (res) { console.log('row count: ' + res.value.length);
	// browser.assert.equal(res.value.length, Object.keys(fruit_garden).length); });  uncheck_filter(browser,
	// 'supplier0');  //supplier1 test_filter_result(browser, lihodeev_supplier, 'supplier1');  browser.elements('css
	// selector', 'app-product-list table tbody tr', function (res) { console.log('row count: ' + res.value.length);
	// browser.assert.equal(res.value.length, Object.keys(lihodeev_supplier).length); });  uncheck_filter(browser,
	// 'supplier1');  //end  browser.pause(2000);
	// browser.expect.element("span[class='header-profile-name']").text.to.equal('Ресторан в доме у
	// Грибоедова').before(10000);  browser.pause(2000); browser.click("span[class='header-profile-name']");
	// browser.click("a[class='button-link header-user-logout']");
	// browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(10000); //Ждём
	// пока не выйдем  },

	// 'Test DEV-1630 filter-price': function (browser) {
	//
	//     // let array_length = browser.globals.vars.length_array;
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 5000, function () {
	//         console.log('email input appeared...');
	//     });
	//     browser.waitForElementVisible('#password', 5000, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', 5000, function () {
	//         console.log('drop-down trigger appeared appeared...');
	//     });
	//     browser.click('.dropdown-trigger');
	//     browser.click('.dropdown-content a:nth-child(1)');
	//
	//     browser.setValue('#email', 'mixcart@bk.ru');
	//     browser.setValue('#password', 'max999');
	//     browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru');
	//     browser.expect.element('#password').to.have.value.that.equals('max999');
	//     browser.submitForm('form');
	//
	//     browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').after(5000);
	//
	//     browser.click('.guest-form-fields a:nth-child(1)');
	//
	//     browser.waitForElementVisible('.header-profile-name', 5000, function (result) {
	//         console.log('.header-profile-name appeared...');
	//     });
	//
	//     browser.url('http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//Переходим
	// на страницу заказов });  browser.resizeWindow(1980, 3000);  browser.pause(3000);
	// browser.waitForElementVisible('.filters .filter-price', 5000);   //filter-price  browser.click('.filters
	// .filter-price');  browser.pause(1000);  browser.saveScreenshot('test1.png');  browser.setValue('#price-from',
	// ['50', browser.Keys.ENTER]); browser.setValue('#price-to', ['1500', browser.Keys.ENTER]);  browser.click('.filters
	// .filter-price .dropdown-content .dropdown-actions a');  browser.pause(5000); browser.click('.filters
	// .filter-price');  browser.pause(1000);  browser.saveScreenshot('test2.png');  for (key in price_filter) {
	// browser.expect.element('app-product-list table tbody tr:nth-child(' + key + ')
	// .table-name').text.to.equal(price_filter[key][0]).before(5000); browser.expect.element('app-product-list table
	// tbody tr:nth-child(' + key + ') .table-price').text.to.equal(price_filter[key][1]).before(5000); }
	// browser.pause(2000);  browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
	// console.log('row count: ' + res.value.length); browser.assert.equal(res.value.length,
	// Object.keys(price_filter).length); });  //end  browser.pause(2000);
	// browser.expect.element("span[class='header-profile-name']").text.to.equal('Ресторан в доме у
	// Грибоедова').before(10000);  browser.pause(2000); browser.click("span[class='header-profile-name']");
	// browser.click("a[class='button-link header-user-logout']");
	// browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(10000); //Ждём
	// пока не выйдем  },

	'Test DEV-1630 filter-sort': function (browser) {

		// let array_length = browser.globals.vars.length_array;

		browser.url('http://dev.mixcart.ronasit.com/login', () => {
			console.log('Loading http://dev.mixcart.ronasit.com/login...');
		});
		browser.waitForElementVisible('#email', 7000, function () {
			console.log('email input appeared...');
		});
		browser.waitForElementVisible('#password', 7000, function () {
			console.log('password input appeared...');
		});
		browser.waitForElementVisible('.dropdown-trigger', 7000, function () {
			console.log('drop-down trigger appeared appeared...');
		});
		browser.click('.dropdown-trigger');
		browser.click('.dropdown-content a:nth-child(1)');

		browser.setValue('#email', 'mixcart@bk.ru');
		browser.setValue('#password', 'max999');
		browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru');
		browser.expect.element('#password').to.have.value.that.equals('max999');
		browser.submitForm('form');

		browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').after(5000);

		browser.click('.guest-form-fields a:nth-child(2)');

		browser.waitForElementVisible('.header-profile-name', 7000, function (result) {
			console.log('.header-profile-name appeared...');
		});

		browser.url('http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
			console.log('Loading http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//Переходим на
		                                                                                                 // страницу
		                                                                                                 // заказов
		});

		browser.resizeWindow(1980, 3000);

		browser.pause(3000);

		browser.waitForElementVisible('.filters .filter-sort', 5000);

		//filter-sort

		function test_filter(b, n, array, msg) {
			b.execute(function (n) {
					let a = document.querySelectorAll('.filters .filter-sort .dropdown-content a');
					a[n].click();
					console.log(msg)
				},
				[n]);
			b.pause(7000);

			for (let key in array) {
				b.expect.element('app-product-list table tbody tr:nth-child(' + key + ') td:nth-child(1)').text.to.equal(array[key][0]).before(5000);
				b.expect.element('app-product-list table tbody tr:nth-child(' + key + ') td:nth-child(3)').text.to.equal(array[key][1]).before(5000);
				b.expect.element('app-product-list table tbody tr:nth-child(' + key + ') td:nth-child(4)').text.to.equal(array[key][2]).before(5000);
			}

			b.saveScreenshot('test_' + n + '.png');

		}

		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('filter a_ya');
		});
		test_filter(browser, 0, filter_sort_a_ya, 'filter a_ya');
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('row count: ' + res.value.length);
			browser.verify.equal(res.value.length, Object.keys(filter_sort_a_ya).length);
		});
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('filter ya_a');
		});
		test_filter(browser, 1, filter_sort_ya_a, 'filter ya_a');
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('row count: ' + res.value.length);
			browser.verify.equal(res.value.length, Object.keys(filter_sort_ya_a).length);
		});
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('filter price up');
		});
		test_filter(browser, 2, filter_sort_price_up, 'filter price up');
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('row count: ' + res.value.length);
			browser.verify.equal(res.value.length, Object.keys(filter_sort_price_up).length);
		});
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('filter price down');
		});
		test_filter(browser, 3, filter_sort_price_down, 'filter price down');
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('row count: ' + res.value.length);
			browser.verify.equal(res.value.length, Object.keys(filter_sort_price_down).length);
		});
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('filter supplier');
		});
		test_filter(browser, 4, filter_sort_supplier, 'filter supplier');
		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('row count: ' + res.value.length);
			browser.verify.equal(res.value.length, Object.keys(filter_sort_supplier).length);
		});

		// browser.click('.filters .filter-sort');
		//
		// browser.pause(1000);
		//
		// browser.saveScreenshot('test1.png');
		//
		// browser.pause(1000);
		//
		// browser.click('.filters .filter-sort');
		//
		// browser.pause(1000);
		//
		// browser.saveScreenshot('test2.png');
		//
		// browser.pause(2000);

		//end

		browser.pause(2000);

		browser.expect.element("span[class='header-profile-name']").text.to.equal('Ресторан в доме у Грибоедова').before(10000);

		browser.pause(2000);
		browser.click("span[class='header-profile-name']");
		browser.click("a[class='button-link header-user-logout']");
		browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(10000); //Ждём пока
	                                                                                                          // не
	                                                                                                          // выйдем

	},
};
