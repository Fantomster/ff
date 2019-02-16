let initial_list =
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

let result_rice = {
	'1': ["рис белый", "ОАО \"Лиходеев-Поставщик\"", "40 RUB"],
	'2': ["рис бурый", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'3': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	'4': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	'5': ["рис черный", "ОАО \"Лиходеев-Поставщик\"", "150 RUB"],
};

let result_apple = {
	'1': ["яблоки голден", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
	'2': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB"],
	'3': ["яблоки сезонные", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"]
};

let result_a =
	{
		'1': ["апельсин", "Ко Фруктовый сад", "800 RUB"],
		'2': ["арбуз", "Ко Фруктовый сад", "200 RUB"],
		'3': ["виноград", "Ко Фруктовый сад", "1500 RUB"],
		'4': ["вода родниковая", "Ко Фруктовый сад", "300 RUB"],
		'5': ["гречневая каша", "ОАО \"Лиходеев-Поставщик\"", "90 RUB"],
		'6': ["груша", "Ко Фруктовый сад", "600 RUB"],
		'7': ["клюква", "Ко Фруктовый сад", "1111 RUB"],
		'8': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
		'9': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB"],
		'10': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB"],
		'11': ["нектарины", "ОАО \"Лиходеев-Поставщик\"", "140 RUB"],
		'12': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
		'13': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
		'14': ["свекла", "ОАО \"Лиходеев-Поставщик\"", "25 RUB"],
		'15': ["фасоль", "ОАО \"Лиходеев-Поставщик\"", "100 RUB"],
	};

let result_m =
	{
		'1': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB"],
		'2': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB"],
		'3': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB"],
	};

let result_pa =
	{
		'1': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB"],
		'2': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB"],
	};

let search_words = ['рис', 'яблоки', 'а', 'м', 'па'];

let array_with_results = [result_rice, result_apple, result_a, result_m, result_pa];

module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1631...');
		console.log('Search test...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1631...');
		browser.end();
	},

	'Test DEV-1631 testing search': function (browser) {

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

		browser.click('.guest-form-fields a:nth-child(1)');

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

		browser.waitForElementVisible('.filters .filter-sort', 7000);

		//search test

		function test_search(b, search_word, array_result, array_initial) {
			b.setValue('header app-search-bar .header-search input', [search_word], function (res) {
				console.log('searching ' + search_word + '...');
			});
			// b.pause('5000');
			for (key in array_result) {
				b.expect.element('app-product-list table tbody tr:nth-child(' + key + ') td:nth-child(1)').text.to.equal(array_result[key][0]).before(8000);
			}
			browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
				console.log('row count: ' + res.value.length);
				browser.verify.equal(res.value.length, Object.keys(array_result).length);
			});
			// b.saveScreenshot('search' + search_word + '.png');
			b.element('css selector', 'header app-search-bar .header-search input', function () {
				for (let i = 0; i < search_word.length; ++i) {
					b.keys("\uE003"); // Жмём backspace
				}
			});
			//b.pause(2000);
			for (key in array_initial) {
				b.expect.element('app-product-list table tbody tr:nth-child(' + key + ') td:nth-child(1)').text.to.equal(array_initial[key][0]).before(8000);
			}
			browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
				console.log('row count: ' + res.value.length);
				browser.verify.equal(res.value.length, Object.keys(array_initial).length);
			});
			// b.saveScreenshot('search_' + search_word + '.png');
		}

		for (let j = 0; j < search_words.length; ++j) {
			test_search(browser, search_words[j], array_with_results[j], initial_list);
		}

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
