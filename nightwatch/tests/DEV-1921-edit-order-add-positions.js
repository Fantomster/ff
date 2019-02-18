let delay = 10000;
let orderSize = 2;
let countToAdd = 5;
module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1921...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1921...');
		browser.end();
	},

	'Test DEV-1921 edit order comment': function (browser) {

		browser.url('http://dev.mixcart.ronasit.com/login', () => {
			console.log('Loading http://dev.mixcart.ronasit.com/login...');
		});
		browser.waitForElementVisible('#email', 1000, function (res) {
			if (res.value) {
				console.log('email input appeared...');
			} else {
				console.log('email input didnt appeared...');
			}

		});
		browser.waitForElementVisible('#password', delay, function () {
			console.log('password input appeared...');
		});
		browser.waitForElementVisible('.dropdown-trigger', delay, function () {
			console.log('drop-down trigger appeared appeared...');
		});

		browser.click('.dropdown-trigger');
		browser.click('.dropdown-content a:nth-child(1)');

		browser.pause(2000);

		browser.setValue('#email', 'restoran.awesome@yandex.ru');
		browser.setValue('#password', 'Max999');
		browser.expect.element('#email').to.have.value.that.equals('restoran.awesome@yandex.ru').before(delay);
		browser.expect.element('#password').to.have.value.that.equals('Max999').before(delay);
		browser.submitForm('form');

		browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').before(delay);

		browser.waitForElementVisible('.guest-form-fields a:nth-child(1)', delay, function (result) {
			console.log('Mo\'s appeared...');
		});

		browser.expect.element('.guest-form-fields a:nth-child(1)').text.to.match(/^AweSome/);

		browser.click('.guest-form-fields a:nth-child(1)');

		browser.waitForElementVisible('.header-profile .dropdown-trigger .header-profile-name', delay, function (result) {
			console.log('.header-profile-name appeared...');
		});

		browser.expect.element('.header-profile .dropdown-trigger .header-profile-name').text.to.equal('AweSome');

		browser.url('https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
			console.log('Loading https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//
		});

		browser.resizeWindow(1980, 1080);

		// browser.pause(3000);

		browser.expect.element('app-product-list tr[app-product-entry]:nth-child(1)').to.be.visible.before(delay);

		browser.saveScreenshot('tessstt4444444.png');

		function scrolling(n) {
			browser.perform(function () {
				console.log('trying to scroll down....please wait...');
			});
			for (let i = 0; i < n; i++) {
				browser.execute(function () {
					window.scrollBy(0, 10000);
				}, []);
				browser.perform(function () {
					console.log('scrolling...');
				});
				browser.pause(parseInt(delay / 4));
			}
		}

		function scrollToBegin() {
			browser.perform(function () {
				console.log('scroll to the window top');
			});
			browser.execute(function () {
				window.scrollTo(0, 0);
			}, []);
		}

		// scrolling(3);
		// scrollToBegin();

		function test_delivery_date(b, supplier_selector, days_to_wait, delay) {
			function zeroBeforeMonth(m) {
				let mArray = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'];
				if (!(m in mArray)) {
					return false;
				}
				let intM = parseInt(m);
				++intM;
				if (intM <= 9) {
					return '0' + String(intM);
				} else return m;
			}

			let selector_date_button = supplier_selector + ' .checkout-table-supplier .checkout-date a';

			b.waitForElementPresent(selector_date_button, delay, true, function () {
				console.log('the button to set delivery date is here...');
			});

			let current_date = new Date();

			let current_month = current_date.getMonth();

			let current_day = current_date.getDate();

			current_date.setDate(1);

			let first_weekday_this_month = current_date.getDay();

			current_date.setDate(current_day + days_to_wait);

			let delivery_date_month = current_date.getMonth();

			let delivery_day = current_date.getDate();

			let delivery_day_weekday = current_date.getDay();

			let n_row, n_col;

			let days_begin = [1, 7, 6, 5, 4, 3, 2];
			let days_end = [7, 1, 2, 3, 4, 5, 6];

			if (delivery_day <= days_begin[first_weekday_this_month]) {
				n_row = 1;
			} else if (delivery_day >= (days_begin[first_weekday_this_month]) && delivery_day <= (days_begin[first_weekday_this_month] + days_end[delivery_day_weekday])) {
				n_row = 2;
			} else {
				n_row = 2 + parseInt((delivery_day - days_begin[first_weekday_this_month] - days_end[delivery_day_weekday]) / 7);
			}
			if (current_date.getDay() === 0) {
				n_col = 7;
			} else {
				n_col = current_date.getDay();
			}
			b.element('css selector', 'body', function () {
				console.log('n_row=' + n_row);
				console.log('n_col=' + n_col);
			});
			b.click(selector_date_button);
			b.pause(2000);
			let selector_date = supplier_selector + ' .checkout-table-supplier .checkout-date .datepicker table tr:nth-child(' + n_row + ') td:nth-child(' + n_col + ')';
			b.click(selector_date);
			b.pause(2000);
			b.click(selector_date_button);
			b.pause(3000);
			let strDate = delivery_day + '.' + zeroBeforeMonth(current_date.getMonth()) + '.' + current_date.getFullYear();
			b.element('css selector', 'body', function () {
				console.log('delivery date = ' + strDate);
			});
			let selector_date_text = supplier_selector + ' .checkout-table-supplier .checkout-date strong';
			b.waitForElementPresent(selector_date_text, delay, true, function () {
				// console.log('the button to set delivery date is here...');
			});
			b.verify.containsText(selector_date_text, strDate);
			b.pause(2000);
		}

		function checkOrderCountByStatus(orderStatusSelector, statusName) {
			let orderStatusCount;
			browser.getText(orderStatusSelector, function (result) {
				orderStatusCount = parseInt(result.value);
				browser.elements('css selector', '.history-table tr[class="history-orders-item ng-star-inserted"]', function (result) {
						if (result.value.length === orderStatusCount) {
							console.log('Фильтрация по статусу "' + statusName + '" прошла успешно');
						} else {
							console.log('Фильтрация по статусу "' + statusName + '" прошла не так как ожидалось');
						}
					}
				);
			});
			if (orderStatusCount) {
				return orderStatusCount;
			}
		}

		function makeOrder(orderSize) {
			//Добавляем товары в корзину
			for (let i = 1; i <= orderSize; i++) {
				let productSelector = 'app-product-list tr[app-product-entry]:nth-child(' + i + ')';
				let addProductSelector = productSelector + ' td:nth-child(6) .quantity-plus';
				let productTitleSelector = productSelector + ' td:nth-child(1)';
				browser.click(addProductSelector);
				browser.pause(500);
				browser.getText(productTitleSelector, function (result) {
					let productName = result.value;
					let firstProductNameSelector = 'app-order div[class="cart-item"]:nth-child(' + String(i + 1) + ') app-item .cart-item-title';
					browser.expect.element(firstProductNameSelector).text.to.equal(productName);
				})
			}

			function check_order_size(selector, comment) {
				browser.elements('css selector', selector, function (result) {
					console.log(comment);
					this.verify.equal(result.value.length, orderSize);
				});
			}

			//проверяем что количество продуктов в корзине на странице с товарами совпадает
			check_order_size('app-order div.cart-item', '\nПроверяем, что количество товаров в корзине такое, как задумано...\n');

			//Проверяем позиции в заказе
			for (let i = 1; i <= orderSize; i++) {
				let productSelector = 'app-product-list tr[app-product-entry]:nth-child(' + i + ')';
				let productTitleSelector = productSelector + ' td:nth-child(1)';
				browser.getText(productTitleSelector, function (result) {
					let productName = result.value;
					let orderButtonSelector = '.cart-buttons div:nth-child(2) a';
					browser.click(orderButtonSelector);
					let productInCartSelector = '.checkout-table-goods tbody tr:nth-child(' + i + ')';
					browser.waitForElementPresent(productInCartSelector, delay);
					let productTitleSelector = productInCartSelector + ' td:nth-child(1)';
					browser.expect.element(productTitleSelector).text.to.equal(productName);
				});
				if (i === orderSize) {
					break;
				}
				browser.url('https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
					console.log('Loading https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//
				});
				productSelector = 'app-product-list tr[app-product-entry]:nth-child(' + String(i + 1) + ')';
				browser.waitForElementPresent(productSelector, delay);
				let orderButtonSelector = '.cart-buttons div:nth-child(2) a';
				browser.waitForElementPresent(orderButtonSelector, delay);
			}

			//проверяем что количество продуктов в корзине совпадает
			browser.saveScreenshot('incart.jpg');
			check_order_size('.checkout-table-goods tbody tr', '\nПроверяем, что количество товаров в корзине такое, как задумано...\n');

			//Устанавливаем дату заказа
			test_delivery_date(browser, '', 1, delay);
			//Нажимаем на кнопку оформить
			let formOrderButtonSelector = '.checkout-table-actions a:nth-child(2)';
			browser.click(formOrderButtonSelector);
			//Ждём пока корзина не очистится
			browser.waitForElementVisible('.checkout-empty-content a[class="button"]', delay, false);

			//Переходим на страницу истории заказов
			browser.url('https://dev.mixcart.ronasit.com/client/history', () => {
				console.log('Loading https://dev.mixcart.ronasit.com/client/history...');
			});

			let firstOrderInHistorySelector = 'app-history-orders .history-table tbody tr:nth-child(1)';
			browser.waitForElementVisible(firstOrderInHistorySelector, delay, false);

			//Проверяем что номер заказа в истории совпадает с номером заказа в карточке заказа
			let orderNumberSelector = firstOrderInHistorySelector + ' td:nth-child(1)';
			browser.getText(orderNumberSelector, function (result) {
				let orderNumber = result.value;
				let goToOrderSelector = orderNumberSelector + ' a';
				browser.click(goToOrderSelector);
				let orderTitleSelector = '.order-head .order-title';
				browser.waitForElementVisible(orderTitleSelector, delay, false);
				let re = new RegExp(orderNumber);
				browser.expect.element(orderTitleSelector).text.to.match(re).before(delay);
			});

			//Проверяем, что позиции товаров в истории верные
			for (let i = 1; i <= orderSize; i++) {
				let orderHistoryProductRowSelector = '.history-order-table tbody tr:nth-child(' + i + ')';
				browser.waitForElementPresent(orderHistoryProductRowSelector, delay, false);
				let orderHistoryProductName = orderHistoryProductRowSelector + ' td:nth-child(1)';
				browser.getText(orderHistoryProductName, function (result) {
					let productName = result.value;
					browser.url('https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
						console.log('Loading https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');
					});
					let productRowSelector = 'app-product-list tr[app-product-entry]:nth-child(' + i + ')';
					browser.waitForElementPresent(productRowSelector, delay, false);
					let productNameSelector = productRowSelector + ' td:nth-child(1)';
					browser.expect.element(productNameSelector).text.to.equal(productName);
					browser.back();
				});
			}

			browser.waitForElementPresent('.history-order-table tbody tr', delay);
			check_order_size('.history-order-table tbody tr', '\nПроверяем, что количество товаров в карточке истории заказа верное...\n');
		}

		function addNewProducts(orderSize, countToAdd) {
			let orderMenuSelector = '.order-menu a';
			browser.click(orderMenuSelector);
			browser.pause(200);
			let editOrderSelector = '.order-menu .dropdown-content .dropdown-item:nth-child(1)';
			browser.click(editOrderSelector);

			function check_order_size(selector, comment) {
				browser.elements('css selector', selector, function (result) {
					console.log(comment);
					this.verify.equal(result.value.length, orderSize + 1);
				});
			}

			browser.waitForElementPresent('.history-order-table tbody', delay, false);

			browser.saveScreenshot('edit_order.jpg');
			check_order_size('.history-order-table tbody tr', '\nПроверяем, что количество товаров в режиме редакторование верное...\n');
			let addProductsLinkSelector = '.history-order-table tbody tr:nth-child(1) a';
			browser.click(addProductsLinkSelector);
			browser.pause(500);

			let addProductTableBodySelector = 'app-product-list table tbody';
			browser.waitForElementPresent(addProductTableBodySelector, delay, false);

			// scrolling(3);
			browser.resizeWindow(1920, 5000);
			browser.pause(20000);
			let addProductTableRowsSelector = addProductTableBodySelector + ' tr';
			browser.elements('css selector', addProductTableRowsSelector, function (result) {
				console.log(result.value.length);
			});
			browser.saveScreenshot('add_products_window.jpg');
		}

		makeOrder(orderSize);

		addNewProducts(orderSize, countToAdd);

		browser.pause(2000);
		browser.click("span[class='header-profile-name']");
		browser.click("a[class='button-link header-user-logout']");
		browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока
	                                                                                                          // не
	                                                                                                          // выйдем
	},
};
