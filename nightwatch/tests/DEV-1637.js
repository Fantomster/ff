let products_list =
	{
		'1': ["апельсин", "Ко Фруктовый сад", "800 RUB", 13],
		'2': ["арбуз", "Ко Фруктовый сад", "200 RUB", 5],
		'3': ["виноград", "Ко Фруктовый сад", "1500 RUB", 12],
		'4': ["вода родниковая", "Ко Фруктовый сад", "300 RUB", 2],
		'5': ["горох", "ОАО \"Лиходеев-Поставщик\"", "40 RUB", 6],
		'6': ["гречневая каша", "ОАО \"Лиходеев-Поставщик\"", "90 RUB", 11],
		'7': ["груша", "Ко Фруктовый сад", "600 RUB", 11],
		'8': ["дыня", "Ко Фруктовый сад", "100 RUB", 5],
		'9': ["клюква", "Ко Фруктовый сад", "1111 RUB", 5],
		'10': ["крупа пшеничная", "ОАО \"Лиходеев-Поставщик\"", "70 RUB", 12],
		'11': ["манная каша", "ОАО \"Лиходеев-Поставщик\"", "45 RUB", 7],
		'12': ["маш", "ОАО \"Лиходеев-Поставщик\"", "105 RUB", 7],
		'13': ["нектарины", "ОАО \"Лиходеев-Поставщик\"", "140 RUB", 8],
		'14': ["персики", "ОАО \"Лиходеев-Поставщик\"", "120 RUB", 13],
		'15': ["пшено", "ОАО \"Лиходеев-Поставщик\"", "50 RUB", 6],
		'16': ["рис белый", "ОАО \"Лиходеев-Поставщик\"", "40 RUB", 5],
		'17': ["рис бурый", "ОАО \"Лиходеев-Поставщик\"", "100 RUB", 10],
		'18': ["рис красный", "ОАО \"Лиходеев-Поставщик\"", "100 RUB", 11],
		'19': ["рис пропаренный", "ОАО \"Лиходеев-Поставщик\"", "50 RUB", 10],
		'20': ["рис черный", "ОАО \"Лиходеев-Поставщик\"", "150 RUB", 5],
		'21': ["свекла", "ОАО \"Лиходеев-Поставщик\"", "25 RUB", 13],
		'22': ["укроп", "Ко Фруктовый сад", "111 RUB", 5],
		'23': ["фасоль", "ОАО \"Лиходеев-Поставщик\"", "100 RUB", 12],
		'24': ["финик", "Ко Фруктовый сад", "3009 RUB", 10],
		'25': ["яблоки голден", "ОАО \"Лиходеев-Поставщик\"", "90 RUB", 9],
		'26': ["яблоки гренни смит", "ОАО \"Лиходеев-Поставщик\"", "80 RUB", 14],
		'27': ["яблоки сезонные", "ОАО \"Лиходеев-Поставщик\"", "70 RUB", 8]
	};

let array_first_supplier = ['1', '2', '3', '4', '9', '24']; //Фруктовый сад
let array_second_supplier = ['5', '6', '10', '11', '23', '26', '27'];//Лиходеев

let suppliers = [array_first_supplier, array_second_supplier];

let suppliers_name = ["Ко Фруктовый сад", "ОАО \"Лиходеев-Поставщик\""];

let delay = 10000; //задержка

let products_to_cart = 4; // количество товаров в корзине

let click_count_plus = 4; // количество кликов на плюсик

let click_count_minus = 2; // количество кликов на минусик

let count_to_delete = 1;

let days_for_delivery = 3; //количество дней для ожидания доставки

module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1637...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1637...');
		browser.end();
	},

	'Test DEV-1637 clearing order': function (browser) {

		let commonOperations = require("./commonoperations");

		// let array_length = browser.globals.vars.length_array;

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

		browser.setValue('#email', 'mixcart@bk.ru');
		browser.setValue('#password', 'max999');
		browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru').before(delay);
		browser.expect.element('#password').to.have.value.that.equals('max999').before(delay);
		browser.submitForm('form');

		browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').before(delay);

		browser.waitForElementVisible('.guest-form-fields a:nth-child(2)', delay, function (result) {
			console.log('\'Ресторан в доме у грибоедова\' appeared...');
		});

		browser.click('.guest-form-fields a:nth-child(2)');

		browser.waitForElementVisible('.header-profile-name', delay, function (result) {
			console.log('.header-profile-name appeared...');
		});

		browser.url('http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
			console.log('Loading http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//Переходим на
		                                                                                                 // страницу
		                                                                                                 // заказов
		});

		browser.resizeWindow(1980, 1080);

		// browser.pause(3000);

		browser.waitForElementVisible('.filters .filter-sort', delay);

		browser.saveScreenshot('tessstt.png');

		browser.perform(function () {
			console.log('trying to scroll down....please wait...');
		});

		for (let i = 0; i < 3; i++) {
			browser.execute(function () {
				window.scrollBy(0, 10000);
			}, []);
			browser.perform(function () {
				console.log('scrolling...');
			});
			browser.pause(parseInt(delay / 4));

		}

		// operations in the cart test

		browser.elements('css selector', 'app-product-list table tbody tr', function (res) {
			console.log('row count: ' + res.value.length);
			browser.verify.equal(res.value.length, Object.keys(products_list).length);
		});

		browser.resizeWindow(1980, 7000);
		browser.saveScreenshot('tesssstttt___full llist.png');

		browser.resizeWindow(1980, 1080);

		// function getRandomInt(min, max) {
		//     return Math.floor(Math.random() * (max - min)) + min;
		// }
		//
		// function (count) {
		//     let array_clicks = [];
		//
		//     for (let j = 1; j <= count; ++j) {
		//         array_clicks.push(getRandomInt(2, 5));
		//     }
		//     return array_clicks;
		// }

		let k = 1;
		let price_sum = 0;

		function test_adding_products_to_cart_from_supplier(b, array, supplier_name, supplier_number, supplier_products, array_clicks, longDelay) {
			b.element('css selector', 'body', function () {
				console.log('Adding products to cart from ' + supplier_name);
			});

			let i = 1;
			let f = true;
			let j = 0;

			let supplier_total_price = 0;

			let supplier_product_counts = [];
			let supplier_product_prices = [];

			for (let key in supplier_products) {
				let str = String(array_clicks[parseInt(key)]);
				b.perform(function () {
					console.log('i will make ' + str + ' clicks now...');
				});
				let productKey = supplier_products[key];
				let plus_selector = 'app-product-list table tbody tr:nth-child(' + productKey + ') td:nth-child(6) .quantity-plus';
				for (let clickCounter = 0; clickCounter < array_clicks[j]; clickCounter++) {
					b.click(plus_selector, function () {
						console.log('tried to add a product');
						console.log(plus_selector);
					});
					b.perform(function () {
						console.log('app-product-list table tbody tr:nth-child(' + supplier_products[key] + ') td:nth-child(6) .quantity-plus');
					});
					if (i === 2 && f) {
						b.waitForElementVisible('.cart-contents .box div:nth-child(' + supplier_number + ') app-order h4:nth-child(1)', longDelay * 2);
						b.verify.containsText('.cart-contents .box div:nth-child(' + supplier_number + ') app-order h4:nth-child(1)', supplier_name);
						f = false;
					}
					b.expect.element('app-product-list table tbody tr:nth-child(' + productKey + ') td:nth-child(6) input').to.have.value.that.equals(String(array[productKey][3] * (clickCounter + 1))).after(delay);
					b.pause(1000);
					b.saveScreenshot('product_added_' + k + '_' + clickCounter + '.png');
				}

				b.pause(parseInt(longDelay / 2));

				++i;

				// b.saveScreenshot('product_added_' + k + '.png');

				b.waitForElementPresent('.cart-contents .box div:nth-child(' + supplier_number + ') app-order .cart-item:nth-child(' + String(i) + ')', longDelay * 2);

				b.perform(function () {
					console.log('product №' + String(parseInt(key) + 1) + ' added');
					console.log('its multiplicity is ' + array[supplier_products[key]][3]);
				});

				let r_products_in_cart = new RegExp("^" + String(k) + "");

				let price_for_current_product = parseInt(parseFloat(array[supplier_products[key]][2]) * array[supplier_products[key]][3] * array_clicks[j]);
				supplier_product_prices[j] = price_for_current_product;
				//
				b.perform(function () {
					console.log('price for current product = ' + price_for_current_product);
				});
				//
				price_sum += price_for_current_product;
				supplier_total_price += price_for_current_product;

				//
				let r_total_price = new RegExp("^" + String(price_sum) + '\\sRUB$');
				//
				b.expect.element('app-cart .cart .cart-total div').text.to.match(r_products_in_cart).before(parseInt(longDelay * 2)); //Проверяем количество товаров в корзине
				b.expect.element('app-cart .cart .cart-total div strong').text.to.match(r_total_price).before(2 * longDelay);//Проверяем
				                                                                                                             // цену
				                                                                                                             // корзину
				//
				// b.pause(parseInt(delay / 3));
				//
				let productCount = String(parseFloat(array[supplier_products[key]][3]) * array_clicks[j]); //количество товара
			                                                                                             // отдельной позиции

				supplier_product_counts[j] = productCount;
				//
				b.verify.attributeEquals('app-product-list table tbody tr:nth-child(' + supplier_products[key] + ') td:nth-child(6) input', 'value', productCount); //проверяем количество товара в списке товаров
				//
				b.verify.attributeEquals('.cart-contents .box div:nth-child(' + supplier_number + ') app-order .cart-item:nth-child(' + String(i) + ') input', 'value', productCount); //проверяем количество товара в корзине
				//
				b.verify.containsText('.cart-contents .box div:nth-child(' + supplier_number + ') app-order .cart-item:nth-child(' + String(i) + ') .cart-item-title', array[supplier_products[key]][0]); //проверяем название товара в корзине

				++j;
				++k;
			}
			return [supplier_total_price, supplier_product_counts, supplier_product_prices];
		}

		function test_checkout_order_operation(b, product_list, supplier_number, supplier_name, supplier_products, supplier_counts, supplier_total_sum, operation, array_clicks, longDelay) {
			b.element('css selector', 'body', function () {
				console.log('operation ' + operation + ' with ' + supplier_name);
			});
			let j = 0;

			let supplier_product_counts = [];
			let supplier_product_prices = [];

			let selector_table_goods = '.checkout-content app-checkout-order:nth-child(' + supplier_number + ') .checkout-table-goods tbody';
			let selector_total_product_price = '.checkout-content app-checkout-order:nth-child(' + supplier_number + ') .checkout-table-supplier .checkout-sum strong';
			for (let i = 1; i <= supplier_products.length; ++i) {
				let str = String(array_clicks[i - 1]);
				b.perform(function () {
					console.log('i will make ' + str + ' clicks now...');
				});
				let productKey = supplier_products[i - 1];
				let selector_product_operation = selector_table_goods + ' tr:nth-child(' + i + ') .table-quantity ' + operation;
				let selector_product_input = selector_table_goods + ' tr:nth-child(' + i + ') .table-quantity input';
				supplier_product_counts[i - 1] = parseInt(supplier_counts[i - 1]);
				for (let clickCounter = 0; clickCounter < array_clicks[i]; clickCounter++) {
					b.click(selector_product_operation, function () {
						console.log('doing operation ' + operation + '...');
					});
					if (operation === '.quantity-plus') {
						supplier_product_counts[i - 1] += parseInt(product_list[productKey][3]);
						b.expect.element(selector_product_input).to.have.value.that.equals(String(supplier_product_counts[i - 1])).before(delay);
					} else if (operation === '.quantity-minus') {
						supplier_product_counts[i - 1] -= parseInt(product_list[productKey][3]);
						b.expect.element(selector_product_input).to.have.value.that.equals(String(supplier_product_counts[i - 1])).before(delay);
					}
					b.saveScreenshot('product_added_' + k + '_' + clickCounter + '.png');
				}
				let price_for_current_product = parseInt(parseFloat(product_list[supplier_products[i - 1]][2]) * supplier_product_counts[i - 1]);//Цена текущего продукта
				supplier_product_prices[j] = price_for_current_product;
				if (operation === '.quantity-plus') {
					supplier_total_sum += array_clicks[i] * parseFloat(product_list[productKey][2]) * product_list[productKey][3];
				} else if (operation === '.quantity-minus') {
					supplier_total_sum -= array_clicks[i] * parseFloat(product_list[productKey][2]) * product_list[productKey][3];
				}
				//
				b.perform(function () {
					console.log('price for current product = ' + price_for_current_product);
				});
				//
				let r_current_product_price = new RegExp("^" + String(price_for_current_product) + '\\sRUB$');
				let r_total_sum = new RegExp("^" + String(supplier_total_sum) + '\\sRUB$');
				//
				let selector_current_product_price = selector_table_goods + ' tr:nth-child(' + i + ') .table-sum';
				b.expect.element(selector_current_product_price).text.to.match(r_current_product_price).before(longDelay);//Проверяем
			                                                                                                            // цену
			                                                                                                            // текущего
			                                                                                                            // товара

				b.expect.element(selector_total_product_price).text.to.match(r_total_sum).before(longDelay);//Проверяем общую
			                                                                                              // стоимость заказа
			}
			return [supplier_total_sum, supplier_product_counts, supplier_product_prices];
		}

		function test_checking_order(b, product_list, suppliers_products, suppliers_names, supplier_cart_info, delay) {
			for (let i = 1; i <= suppliers_products.length; ++i) {
				let selector_checkout_table_supplier = '.checkout-content app-checkout-order:nth-child(' + i + ') .checkout-table .checkout-table-supplier';
				b.waitForElementPresent(selector_checkout_table_supplier, delay, false, function () {
					console.log('supplier  №' + i + ' appeared...');
				});
				let selector_supplier_name = selector_checkout_table_supplier + ' .checkout-supplier';
				b.waitForElementPresent(selector_supplier_name, delay, false, function () {
					console.log('supplier  №' + i + ' name appeared...');
				});
				b.verify.containsText(selector_supplier_name, suppliers_names[i - 1]);
				let supplier_sum = supplier_cart_info[i - 1][0] + ' RUB';
				let selector_supplier_sum = selector_checkout_table_supplier + ' .checkout-sum strong';
				b.waitForElementPresent(selector_supplier_sum, delay, false, function () {
					console.log('supplier  №' + i + ' total appeared...');
				});
				b.verify.containsText(selector_supplier_sum, supplier_sum);
				let selector_checkout_table_goods = '.checkout-content app-checkout-order:nth-child(' + i + ') .checkout-table .checkout-table-goods';
				b.waitForElementPresent(selector_checkout_table_goods, delay, false, function () {
					console.log('supplier  №' + i + ' goods table appeared...');
				});
				for (let j = 1; j < suppliers_products[i - 1].length; ++j) {
					let selector_row = selector_checkout_table_goods + ' tbody tr:nth-child(' + j + ')';
					b.waitForElementPresent(selector_row, delay, false, function () {
						console.log('supplier  №' + i + ' product ' + j + ' appeared...');
					});
					let selector_row_title = selector_row + ' .table-item';
					b.waitForElementPresent(selector_row_title, delay, false, function () {
						console.log('supplier  №' + i + ' product ' + j + ' appeared...');
					});
					b.verify.containsText(selector_row_title, product_list[suppliers_products[i - 1][j - 1]][0]);
					let selector_row_count = selector_row + ' .table-quantity input';
					b.waitForElementPresent(selector_row_count, delay, false, function () {
						console.log('supplier  №' + i + ' product ' + j + ' appeared...');
					});
					b.verify.valueContains(selector_row_count, String(supplier_cart_info[i - 1][1][j - 1]));
					let selector_row_sum = selector_row + ' .table-sum';
					b.waitForElementPresent(selector_row_sum, delay, false, function () {
						console.log('supplier  №' + i + ' product ' + j + ' appeared...');
					});
					b.verify.containsText(selector_row_sum, String(supplier_cart_info[i - 1][2][j - 1]) + ' RUB');
				}
			}
		}

		function test_orders_comments(b, supplier_products, comment_text, delay) {
			let selectors = [];
			let k = 0;
			for (let j = 1; j <= supplier_products.length; ++j) {
				let selector_checkout_order = '.checkout-content app-checkout-order:nth-child(' + String(j) + ')';
				let selector_modal = 'app-comment-modal .popup-overlay .popup-form';
				let selector_modal_text_area = selector_modal + ' form textarea';
				let selector_modal_button = selector_modal + ' form a';

				let selector_checkout_table_supplier = selector_checkout_order + ' .checkout-table .checkout-table-supplier';
				let selector_supplier_comment_button = selector_checkout_table_supplier + ' .checkout-comments a';
				b.click(selector_supplier_comment_button, function () {
					console.log('clicking on order comment link...')
				});

				b.waitForElementPresent(selector_modal, delay, true, function () {
					console.log('comment form for supplier ' + j + ' appeared');
				});
				b.waitForElementPresent(selector_modal_text_area, delay, true, function () {
					// console.log('comment form for supplier ' + i + ' and product ' + j + ' appeared');
				});
				b.waitForElementPresent(selector_modal_button, delay, true, function () {
					// console.log('comment form for supplier ' + i + ' and product ' + j + ' appeared');
				});
				b.setValue(selector_modal_text_area, comment_text);
				b.pause(1000);
				b.verify.attributeEquals(selector_modal_text_area, 'value', comment_text);
				let selector_supplier_comment_text = selector_checkout_table_supplier + ' .checkout-comments .checkout-comments-value';
				b.click(selector_modal_button, function () {
					console.log('adding comment to product');
					console.log(selector_supplier_comment_text);
				});
				b.waitForElementNotPresent(selector_modal, delay, true, function () {
					// console.log('comment form for product ' + i + ' disappeared');
				});
				b.pause(3000);

				// selectors[k] = selector_supplier_comment_text;
				b.expect.element(selector_supplier_comment_text).text.to.equal(comment_text).before(delay);
				b.pause(7000);
				b.saveScreenshot('commentttt_' + j + '.jpg');
				b.pause(3000);
				b.end();
				return false;
				// if (k > 0) {
				//     b.expect.element(selectors[k]).text.to.equal("").before(delay);
				// }
				// k++;

				for (let i = 1; i <= supplier_products[j - 1].length; ++i) {
					let selector_row_comment = selector_checkout_order + ' .checkout-table-goods table tbody tr:nth-child(' + String(i) + ') .table-comment';
					let selector_comment_button = selector_row_comment + ' a';
					b.click(selector_comment_button);
					b.waitForElementPresent(selector_modal, delay, true, function () {
						console.log('comment form for supplier ' + j + ' and product ' + i + ' appeared');
					});
					b.waitForElementPresent(selector_modal_text_area, delay, true, function () {
						// console.log('comment form for supplier ' + i + ' and product ' + j + ' appeared');
					});
					b.waitForElementPresent(selector_modal_button, delay, true, function () {
						// console.log('comment form for supplier ' + i + ' and product ' + j + ' appeared');
					});
					b.setValue(selector_modal_text_area, comment_text);
					b.pause(1000);
					b.verify.attributeEquals(selector_modal_text_area, 'value', comment_text);
					b.click(selector_modal_button, function () {
						console.log('adding comment to product');
					});
					b.waitForElementNotPresent(selector_modal, delay, true, function () {
						console.log('comment form for product ' + i + ' disappeared');
					});
					b.pause(3000);
					let selector_row_comment_text = selector_row_comment + ' .checkout-comment-text';
					b.verify.containsText(selector_row_comment_text, comment_text);
					// b.saveScreenshot('comment_' + i + '.png');
					// browser.expect.element('.checkout-table-goods table tbody tr:nth-child(' + String(i) + ') .table-comment
					// .checkout-comment-text').text.to.equal(comment_text).before(2*delay);
				}
				// b.verify.containsText(selector_supplier_comment_text, comment_text);
				// b.saveScreenshot('all_comments_added.png');
			}
		}

		function test_order_attribute_notification(b, supplier_count, delay) {

			for (let i = supplier_count; i >= 1; --i) {
				let selector_supplier = '.checkout-content app-checkout-order:nth-child(' + i + ')';
				let selector_button_form = selector_supplier + ' .checkout-table-actions a:nth-child(2)';
				b.click(selector_button_form, function () {
					console.log('trying to form a checkout...');
				});
				let selector_notification = 'app-error-notification .notifications .notification-error .notification-text';
				b.waitForElementPresent(selector_notification, delay, true, function (res) {
					console.log('notification appeared...');
				});
				b.saveScreenshot('notification_' + i + '_1.jpg');
				// b.verify.containsText('app-error-notification .notifications .notification-error
				// .notification-text','Укажите дату доставки'); browser.getText("app-error-notification .notifications
				// .notification-error .notification-text", function(result) { this.verify.equal(result.value, "Укажите дату
				// доставки"); });
				b.waitForElementNotVisible(selector_notification, delay, true, function (res) {
					console.log('notification disappeared...');
				});

				test_delivery_date(b, selector_supplier, 3, delay);

				b.pause(3000);

				b.click(selector_button_form, function () {
					console.log('trying to form a checkout...')
				});
				// b.waitForElementNotPresent(selector_supplier, delay, true, function (res) {
				//     console.log('order disappeared...');
				// });
				b.waitForElementPresent(selector_notification, delay, true, function (res) {
					console.log('notification appeared...');
				});
				b.saveScreenshot('notification_' + i + '_2.jpg');
				b.waitForElementNotVisible(selector_notification, delay, true, function (res) {
					console.log('notification disappeared...');
				});
			}
		}

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

		function test_click_form_buttons(b, suppliers, delay) {
			for (let i = suppliers.length; i >= 1; --i) {
				let selector_supplier = '.checkout-content app-checkout-order:nth-child(' + i + ')';
				let selector_button_form = selector_supplier + ' .checkout-table-actions a:nth-child(2)';
				b.click(selector_button_form);
				b.waitForElementNotPresent(selector_supplier, delay, true, function () {
					console.log('the order ' + i + ' formed...');
				});
				let selector_notification = 'app-error-notification .notifications .notification-error .notification-text';
				b.waitForElementVisible(selector_notification, delay, true, function () {
					console.log('notification appeared...');
				});
			}
			let selector_empty_order = 'app-checkout .checkout-empty .checkout-empty-content';
			b.waitForElementVisible(selector_empty_order, delay, true, function () {
				console.log('your cart is empty now...');
			});
			let selector_empty_order_title = selector_empty_order + ' h2';
			b.waitForElementVisible(selector_empty_order_title, delay, true, function () {
				console.log('empty cart title appeared...');
			});
			b.verify.containsText(selector_empty_order_title, 'Корзина пуста');
			let selector_empty_order_link_to_products = selector_empty_order + ' a';
			b.waitForElementVisible(selector_empty_order_link_to_products, delay, true, function () {
				console.log('empty cart link appeared...');
			});
			b.verify.containsText(selector_empty_order_link_to_products, 'Перейти к продуктам');
		}

		function test_cancel_order(b, order_number, delay) {
			let selector_order = '.checkout-content app-checkout-order:nth-child(' + order_number + ')';
			let selector_order_cancel_button = selector_order + ' .checkout-table-actions a:nth-child(1)';
			b.click(selector_order_cancel_button, function () {
				console.log('tried to cancel order ' + order_number + '...');
			});
			b.waitForElementNotPresent(selector_order, delay, true, function () {
				console.log('order ' + order_number + ' canceled...');
			});
		}

		function test_empty_orders(b, delay) {
			let selector_empty_cart = 'app-checkout .checkout-empty';
			b.waitForElementVisible(selector_empty_cart, delay, function () {
				console.log('the cart is empty now...')
			});
		}

		//Добавляем продукты в корзину
		let suppliers_cart_info = [];
		let supplier_clicks = [];
		for (let i = 1; i <= suppliers.length; ++i) {
			let array_clicks = commonOperations.randomArray(suppliers[i - 1].length);
			supplier_clicks[i - 1] = array_clicks;
			suppliers_cart_info[i - 1] = test_adding_products_to_cart_from_supplier(browser, products_list, suppliers_name[i - 1], String(i), suppliers[i - 1], array_clicks, delay);
		}

		// let suppliers_cart_info =
		// commonOperations.test_adding_products_to_cart_from_suppliers(browser,products_list,suppliers_name,suppliers,delay);
		// Переходим на страницу оформления заказа

		let selector_go_to_order_button = 'app-cart .cart-head .cart-buttons div:nth-child(2) a';
		browser.click(selector_go_to_order_button);

		let selector_checkout_head = 'app-checkout .checkout-head';
		browser.waitForElementPresent(selector_checkout_head, delay, false, function () {
		});

		test_checking_order(browser, products_list, suppliers, suppliers_name, suppliers_cart_info, delay);//Проверяем
	                                                                                                     // продукты на
	                                                                                                     // странице
	                                                                                                     // офомления
	                                                                                                     // заказов

		for (let i = suppliers.length; i >= 1; --i) {
			let supplier_number = String(i);
			test_cancel_order(browser, supplier_number, delay);
		}

		test_empty_orders(browser, delay);

		browser.pause(3000);

		//end

		browser.refresh();

		browser.expect.element("span[class='header-profile-name']").text.to.equal('Ресторан в доме у Грибоедова').before(delay);

		browser.pause(2000);
		browser.click("span[class='header-profile-name']");
		browser.click("a[class='button-link header-user-logout']");
		browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока
	                                                                                                          // не
	                                                                                                          // выйдем
	},

	// 'Test DEV-1632 clear cart': function (browser) {
	//
	//     // let array_length = browser.globals.vars.length_array;
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 7000, function () {
	//         console.log('email input appeared...');
	//     });
	//     browser.waitForElementVisible('#password', 7000, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', 7000, function () {
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
	//     browser.waitForElementVisible('.header-profile-name', 7000, function (result) {
	//         console.log('.header-profile-name appeared...');
	//     });
	//
	//     browser.url('http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//Переходим
	// на страницу заказов });  browser.resizeWindow(1980, 5000);  browser.pause(3000);
	// browser.waitForElementVisible('.filters .filter-sort', 7000);  browser.saveScreenshot('tessstt.png');   //cart
	// clear test for (let i = 0; i < 3; i++) { browser.pause(10000); browser.waitForElementVisible('app-cart .cart
	// .cart-head .cart-buttons .button-red', 7000, function () { console.log('clear cart button appeared...'); });
	// browser.waitForElementVisible('app-cart .cart .cart-head .cart-buttons .button-red', 7000, function () {
	// console.log('clear cart button appeared...'); }); browser.click('app-cart .cart .cart-head .cart-buttons
	// .button-red'); browser.pause(10000); browser.waitForElementVisible('app-cart .cart .cart-empty', 7000, function ()
	// { console.log('empty cart appeared...') }); browser.saveScreenshot('empty.cart.' + i + '.png'); browser.refresh();
	// } //end  browser.pause(5000);
	// browser.expect.element("span[class='header-profile-name']").text.to.equal('Mo\'s').before(10000);
	// browser.pause(2000); browser.click("span[class='header-profile-name']"); browser.click("a[class='button-link
	// header-user-logout']"); browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро
	// пожаловать').before(10000); //Ждём пока не выйдем },
};
