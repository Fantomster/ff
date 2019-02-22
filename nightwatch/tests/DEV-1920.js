let delay = 10000;
module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1920...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1920...');
		browser.end();
	},

	// 'Test DEV-1920 order history, status filtration': function (browser) {
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 1000, function (res) {
	//         if (res.value) {
	//             console.log('email input appeared...');
	//         } else {
	//             console.log('email input didnt appeared...');
	//         }
	//
	//     });
	//     browser.waitForElementVisible('#password', delay, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', delay, function () {
	//         console.log('drop-down trigger appeared appeared...');
	//     });
	//     browser.click('.dropdown-trigger');
	//     browser.click('.dropdown-content a:nth-child(1)');
	//
	//     browser.setValue('#email', 'mixcart@bk.ru');
	//     browser.setValue('#password', 'max999');
	//     browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru').before(delay);
	//     browser.expect.element('#password').to.have.value.that.equals('max999').before(delay);
	//     browser.submitForm('form');
	//
	//     browser.saveScreenshot('tessstt000.png');
	//
	//     browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').before(delay);
	//
	//     browser.waitForElementVisible('.guest-form-fields a:nth-child(2)', delay, function (result) {
	//         console.log('Mo\'s appeared...');
	//     });
	//
	//     browser.expect.element('.guest-form-fields a:nth-child(2)').text.to.match(/^Mo\'s/);
	//
	//     browser.click('.guest-form-fields a:nth-child(2)');
	//
	//
	//     browser.waitForElementVisible('.header-profile .dropdown-trigger .header-profile-name', delay, function
	// (result) { console.log('.header-profile-name appeared...'); });  browser.expect.element('.header-profile
	// .dropdown-trigger .header-profile-name').text.to.equal('Mo\'s');
	// browser.url('https://dev.mixcart.ronasit.com/client/history', () => { console.log('Loading
	// https://dev.mixcart.ronasit.com/client/history...');//Переходим на страницу истории заказов });
	// browser.resizeWindow(1980, 1080);  browser.pause(3000);   browser.saveScreenshot('tessstt111.png');   function
	// scrolling(n) { browser.perform(function () { console.log('trying to scroll down....please wait...'); }); for (let
	// i = 0; i < n; i++) { browser.execute(function () { window.scrollBy(0, 10000); }, []); browser.perform(function ()
	// { console.log('scrolling...'); }); browser.pause(parseInt(delay / 4)); } }  function scrollToBegin() {
	// browser.perform(function () { console.log('scroll to the window top'); }); browser.execute(function () {
	// window.scrollTo(0, 0); }, []); }  scrolling(4);   browser.saveScreenshot('tessstt222.png');  function
	// changeStatus(statusLink) { scrollToBegin();  browser.click(statusLink);  scrolling(3); }  function
	// checkOrderCountByStatus(orderStatusSelector, statusName) { browser.getText(orderStatusSelector, function (result)
	// { let orderStatusCount = parseInt(result.value); browser.elements('css selector', '.history-table
	// tr[class="history-orders-item ng-star-inserted"]', function (result) { if (result.value.length ===
	// orderStatusCount) { console.log('Фильтрация по статусу "' + statusName + '" прошла успешно'); } else {
	// console.log('Фильтрация по статусу "' + statusName + '" прошла не так как ожидалось'); } })  } ); }  let
	// pendingStatusSelector = 'a[class="button button-white history-stats-item pending"]';
	// changeStatus(pendingStatusSelector); checkOrderCountByStatus(pendingStatusSelector, "Ожидают подтверждения");
	// browser.saveScreenshot('pending.png');  let processingStatusSelector = 'a[class="button button-white
	// history-stats-item processing"]'; changeStatus(processingStatusSelector); checkOrderCountByStatus(processingStatusSelector, "Выполняются"); browser.saveScreenshot('processing.png');  let cancelledStatusSelector = 'a[class="button button-white history-stats-item cancelled"]'; changeStatus(cancelledStatusSelector); checkOrderCountByStatus(cancelledStatusSelector, "Отменены"); browser.saveScreenshot('cancelled.png');  let completedStatusSelector = 'a[class="button button-white history-stats-item completed"]'; changeStatus(completedStatusSelector); checkOrderCountByStatus(completedStatusSelector, "Завершены"); browser.saveScreenshot('completed.png');   browser.pause(2000); browser.click("span[class='header-profile-name']"); browser.click("a[class='button-link header-user-logout']"); browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока не выйдем browser.saveScreenshot('tessstt444.png'); },

	// 'Test DEV-1920 from order to chat and backwards': function (browser) {
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 1000, function (res) {
	//         if (res.value) {
	//             console.log('email input appeared...');
	//         } else {
	//             console.log('email input didnt appeared...');
	//         }
	//
	//     });
	//     browser.waitForElementVisible('#password', delay, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', delay, function () {
	//         console.log('drop-down trigger appeared appeared...');
	//     });
	//     browser.click('.dropdown-trigger');
	//     browser.click('.dropdown-content a:nth-child(1)');
	//
	//     browser.setValue('#email', 'mixcart@bk.ru');
	//     browser.setValue('#password', 'max999');
	//     browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru').before(delay);
	//     browser.expect.element('#password').to.have.value.that.equals('max999').before(delay);
	//     browser.submitForm('form');
	//
	//     browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').before(delay);
	//
	//     browser.waitForElementVisible('.guest-form-fields a:nth-child(2)', delay, function (result) {
	//         console.log('Mo\'s appeared...');
	//     });
	//
	//     browser.expect.element('.guest-form-fields a:nth-child(2)').text.to.match(/^Mo\'s/);
	//
	//     browser.click('.guest-form-fields a:nth-child(2)');
	//
	//
	//     browser.waitForElementVisible('.header-profile .dropdown-trigger .header-profile-name', delay, function
	// (result) { console.log('.header-profile-name appeared...'); });  browser.expect.element('.header-profile
	// .dropdown-trigger .header-profile-name').text.to.equal('Mo\'s');
	// browser.url('https://dev.mixcart.ronasit.com/client/history', () => { console.log('Loading
	// https://dev.mixcart.ronasit.com/client/history...');//Переходим на страницу истории заказов });
	// browser.resizeWindow(1980, 1080);  browser.pause(3000);   function scrolling(n) { browser.perform(function () {
	// console.log('trying to scroll down....please wait...'); }); for (let i = 0; i < n; i++) { browser.execute(function
	// () { window.scrollBy(0, 10000); }, []); browser.perform(function () { console.log('scrolling...'); });
	// browser.pause(parseInt(delay / 4)); } }  function scrollToBegin() { browser.perform(function () {
	// console.log('scroll to the window top'); }); browser.execute(function () { window.scrollTo(0, 0); }, []); }
	// function goToChat(orderRowSelector) { let orderIdSelector = orderRowSelector + ' td:nth-child(1)';
	// browser.getText(orderIdSelector, function (result) { let orderId = result.value; let orderChatLinkSelector =
	// orderRowSelector + ' td:nth-child(6) a'; browser.click(orderChatLinkSelector);
	// browser.waitForElementVisible('.dialog-order', delay, function (result) { });
	// browser.expect.element('.dialog-order').text.to.contain(orderId).before(delay);
	// browser.waitForElementVisible('.dialog-toorder', delay, function (result) { }); browser.click('.dialog-toorder');
	// browser.waitForElementVisible('.order-title', delay, function (result) { });
	// browser.expect.element('.order-title').text.to.contain(orderId).before(delay); }) }  function getRandomInt(min,
	// max) { return Math.floor(Math.random() * (max - min)) + min; }   scrolling(4);  scrollToBegin();
	// browser.elements('css selector', '.history-table tr[class="history-orders-item ng-star-inserted"]', function
	// (result) { let randNumber = getRandomInt(1, result.value.length); console.log('randNumber = ' + randNumber); let orderRowSelector = '.history-table tr[class="history-orders-item ng-star-inserted"]:nth-child(' + randNumber + ')'; goToChat(orderRowSelector); });   browser.pause(2000); browser.click("span[class='header-profile-name']"); browser.click("a[class='button-link header-user-logout']"); browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока не выйдем browser.saveScreenshot('tessstt444.png'); },

	// 'Test DEV-1920 order history - cancel order': function (browser) {
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 1000, function (res) {
	//         if (res.value) {
	//             console.log('email input appeared...');
	//         } else {
	//             console.log('email input didnt appeared...');
	//         }
	//
	//     });
	//     browser.waitForElementVisible('#password', delay, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', delay, function () {
	//         console.log('drop-down trigger appeared appeared...');
	//     });
	//     browser.click('.dropdown-trigger');
	//     browser.click('.dropdown-content a:nth-child(1)');
	//
	//     browser.setValue('#email', 'mixcart@bk.ru');
	//     browser.setValue('#password', 'max999');
	//     browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru').before(delay);
	//     browser.expect.element('#password').to.have.value.that.equals('max999').before(delay);
	//     browser.submitForm('form');
	//
	//     browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').before(delay);
	//
	//     browser.waitForElementVisible('.guest-form-fields a:nth-child(2)', delay, function (result) {
	//         console.log('Mo\'s appeared...');
	//     });
	//
	//     browser.expect.element('.guest-form-fields a:nth-child(2)').text.to.match(/^Mo\'s/);
	//
	//     browser.click('.guest-form-fields a:nth-child(2)');
	//
	//
	//     browser.waitForElementVisible('.header-profile .dropdown-trigger .header-profile-name', delay, function
	// (result) { console.log('.header-profile-name appeared...'); });  browser.expect.element('.header-profile
	// .dropdown-trigger .header-profile-name').text.to.equal('Mo\'s');
	// browser.url('https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => { console.log('Loading
	// https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');// });  browser.resizeWindow(1980,
	// 1080);  // browser.pause(3000);  browser.expect.element('app-product-list
	// tr[app-product-entry]:nth-child(1)').to.be.visible.before(delay);  browser.saveScreenshot('tessstt4444444.png');
	// function scrolling(n) { browser.perform(function () { console.log('trying to scroll down....please wait...'); });
	// for (let i = 0; i < n; i++) { browser.execute(function () { window.scrollBy(0, 10000); }, []);
	// browser.perform(function () { console.log('scrolling...'); }); browser.pause(parseInt(delay / 4)); } }  function
	// scrollToBegin() { browser.perform(function () { console.log('scroll to the window top'); });
	// browser.execute(function () { window.scrollTo(0, 0); }, []); }  // scrolling(3); // scrollToBegin();  function
	// test_delivery_date(b, supplier_selector, days_to_wait, delay) { function zeroBeforeMonth(m) { let mArray = ['0',
	// '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11']; if (!(m in mArray)) { return false; } let intM =
	// parseInt(m); ++intM; if (intM <= 9) { return '0' + String(intM); } else return m; }  let selector_date_button =
	// supplier_selector + ' .checkout-table-supplier .checkout-date a';  b.waitForElementPresent(selector_date_button,
	// delay, true, function () { console.log('the button to set delivery date is here...'); });  let current_date = new
	// Date();  let current_month = current_date.getMonth();  let current_day = current_date.getDate();
	// current_date.setDate(1);  let first_weekday_this_month = current_date.getDay();  current_date.setDate(current_day
	// + days_to_wait);  let delivery_date_month = current_date.getMonth();  let delivery_day = current_date.getDate();
	// let delivery_day_weekday = current_date.getDay();  let n_row, n_col;  let days_begin = [1, 7, 6, 5, 4, 3, 2]; let days_end = [7, 1, 2, 3, 4, 5, 6];  if (delivery_day <= days_begin[first_weekday_this_month]) { n_row = 1; } else if (delivery_day >= (days_begin[first_weekday_this_month]) && delivery_day <= (days_begin[first_weekday_this_month] + days_end[delivery_day_weekday])) { n_row = 2; } else { n_row = 2 + parseInt((delivery_day - days_begin[first_weekday_this_month] - days_end[delivery_day_weekday]) / 7); } if (current_date.getDay() === 0) { n_col = 7; } else { n_col = current_date.getDay(); } b.element('css selector', 'body', function () { console.log('n_row=' + n_row); console.log('n_col=' + n_col); }); b.click(selector_date_button); b.pause(2000); let selector_date = supplier_selector + ' .checkout-table-supplier .checkout-date .datepicker table tr:nth-child(' + n_row + ') td:nth-child(' + n_col + ')'; b.click(selector_date); b.pause(2000); b.click(selector_date_button); b.pause(3000); let strDate = delivery_day + '.' + zeroBeforeMonth(current_date.getMonth()) + '.' + current_date.getFullYear(); b.element('css selector', 'body', function () { console.log('delivery date = ' + strDate); }); let selector_date_text = supplier_selector + ' .checkout-table-supplier .checkout-date strong'; b.waitForElementPresent(selector_date_text, delay, true, function () { // console.log('the button to set delivery date is here...'); }); b.verify.containsText(selector_date_text, strDate); b.pause(2000); }  function checkOrderCountByStatus(orderStatusSelector, statusName) { let orderStatusCount; browser.getText(orderStatusSelector, function (result) { orderStatusCount = parseInt(result.value); browser.elements('css selector', '.history-table tr[class="history-orders-item ng-star-inserted"]', function (result) { if (result.value.length === orderStatusCount) { console.log('Фильтрация по статусу "' + statusName + '" прошла успешно'); } else { console.log('Фильтрация по статусу "' + statusName + '" прошла не так как ожидалось'); } } ); }); if (orderStatusCount) { return orderStatusCount; } }  function makeOrderAndCancelIt() { let productSelector = 'app-product-list tr[app-product-entry]:nth-child(1)'; let addProductSelector = productSelector + ' td:nth-child(6) .quantity-plus'; let productTitleSelector = productSelector + ' td:nth-child(1)'; browser.getText(productTitleSelector, function (result) { let productName = result.value; let firstProductNameSelector = 'app-order app-item:nth-child(1) div.cart-item-title'; browser.click(addProductSelector); browser.pause(500); browser.expect.element(firstProductNameSelector).text.to.equal(productName); browser.pause(delay / 2); let buttonOrder = ".cart-buttons div:nth-child(2) a"; browser.pause(delay / 2); browser.click(buttonOrder); browser.pause(delay / 2); browser.saveScreenshot('tesssttformorderpage.png'); test_delivery_date(browser, '', 1, delay) browser.saveScreenshot('tesssttformorderpagedateset.png'); browser.click('.checkout-table-actions a:nth-child(2)'); browser.waitForElementVisible('.checkout-empty-content a[class="button"]', delay, false); browser.saveScreenshot('testformorderpagempty.png'); browser.url('https://dev.mixcart.ronasit.com/client/history', () => { console.log('Loading https://dev.mixcart.ronasit.com/client/history...');//Переходим на страницу истории заказов }); browser.pause(delay / 2); browser.saveScreenshot('historypage.png'); let canceledCount; browser.getText('app-history-orders-statuses .cancelled', function (result) { canceledCount = parseInt(result.value); browser.click('.history-table tbody tr[class="history-orders-item ng-star-inserted"]:nth-child(1) td:nth-child(7) a:nth-child(1)'); let newNumber = parseInt(String(canceledCount+1)); let re = new RegExp(newNumber); browser.expect.element('app-history-orders-statuses .cancelled').text.to.match(re).before(delay); browser.expect.element('.history-table tbody tr[class="history-orders-item ng-star-inserted"]:nth-child(1) td:nth-child(7) app-order-statuses-action-button:nth-child(2) a').text.to.equal('ПОВТОРИТЬ').before(delay); }); }); }  makeOrderAndCancelIt();   browser.pause(2000); browser.click("span[class='header-profile-name']"); browser.click("a[class='button-link header-user-logout']"); browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока не выйдем },

	// 'Test DEV-1920 order history - finish order': function (browser) {
	//
	//     browser.url('http://dev.mixcart.ronasit.com/login', () => {
	//         console.log('Loading http://dev.mixcart.ronasit.com/login...');
	//     });
	//     browser.waitForElementVisible('#email', 1000, function (res) {
	//         if (res.value) {
	//             console.log('email input appeared...');
	//         } else {
	//             console.log('email input didnt appeared...');
	//         }
	//
	//     });
	//     browser.waitForElementVisible('#password', delay, function () {
	//         console.log('password input appeared...');
	//     });
	//     browser.waitForElementVisible('.dropdown-trigger', delay, function () {
	//         console.log('drop-down trigger appeared appeared...');
	//     });
	//     browser.click('.dropdown-trigger');
	//     browser.click('.dropdown-content a:nth-child(1)');
	//
	//     browser.setValue('#email', 'mixcart@bk.ru');
	//     browser.setValue('#password', 'max999');
	//     browser.expect.element('#email').to.have.value.that.equals('mixcart@bk.ru').before(delay);
	//     browser.expect.element('#password').to.have.value.that.equals('max999').before(delay);
	//     browser.submitForm('form');
	//
	//     browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').before(delay);
	//
	//     browser.waitForElementVisible('.guest-form-fields a:nth-child(2)', delay, function (result) {
	//         console.log('Mo\'s appeared...');
	//     });
	//
	//     browser.expect.element('.guest-form-fields a:nth-child(2)').text.to.match(/^Mo\'s/);
	//
	//     browser.click('.guest-form-fields a:nth-child(2)');
	//
	//
	//     browser.waitForElementVisible('.header-profile .dropdown-trigger .header-profile-name', delay, function
	// (result) { console.log('.header-profile-name appeared...'); });  browser.expect.element('.header-profile
	// .dropdown-trigger .header-profile-name').text.to.equal('Mo\'s');
	// browser.url('https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => { console.log('Loading
	// https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');// });  browser.resizeWindow(1980,
	// 1080);  // browser.pause(3000);  browser.expect.element('app-product-list
	// tr[app-product-entry]:nth-child(1)').to.be.visible.before(delay);  browser.saveScreenshot('tessstt4444444.png');
	// function scrolling(n) { browser.perform(function () { console.log('trying to scroll down....please wait...'); });
	// for (let i = 0; i < n; i++) { browser.execute(function () { window.scrollBy(0, 10000); }, []);
	// browser.perform(function () { console.log('scrolling...'); }); browser.pause(parseInt(delay / 4)); } }  function
	// scrollToBegin() { browser.perform(function () { console.log('scroll to the window top'); });
	// browser.execute(function () { window.scrollTo(0, 0); }, []); }  // scrolling(3); // scrollToBegin();  function
	// test_delivery_date(b, supplier_selector, days_to_wait, delay) { function zeroBeforeMonth(m) { let mArray = ['0',
	// '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11']; if (!(m in mArray)) { return false; } let intM =
	// parseInt(m); ++intM; if (intM <= 9) { return '0' + String(intM); } else return m; }  let selector_date_button =
	// supplier_selector + ' .checkout-table-supplier .checkout-date a';  b.waitForElementPresent(selector_date_button,
	// delay, true, function () { console.log('the button to set delivery date is here...'); });  let current_date = new
	// Date();  let current_month = current_date.getMonth();  let current_day = current_date.getDate();
	// current_date.setDate(1);  let first_weekday_this_month = current_date.getDay();  current_date.setDate(current_day
	// + days_to_wait);  let delivery_date_month = current_date.getMonth();  let delivery_day = current_date.getDate();
	// let delivery_day_weekday = current_date.getDay();  let n_row, n_col;  let days_begin = [1, 7, 6, 5, 4, 3, 2]; let days_end = [7, 1, 2, 3, 4, 5, 6];  if (delivery_day <= days_begin[first_weekday_this_month]) { n_row = 1; } else if (delivery_day >= (days_begin[first_weekday_this_month]) && delivery_day <= (days_begin[first_weekday_this_month] + days_end[delivery_day_weekday])) { n_row = 2; } else { n_row = 2 + parseInt((delivery_day - days_begin[first_weekday_this_month] - days_end[delivery_day_weekday]) / 7); } if (current_date.getDay() === 0) { n_col = 7; } else { n_col = current_date.getDay(); } b.element('css selector', 'body', function () { console.log('n_row=' + n_row); console.log('n_col=' + n_col); }); b.click(selector_date_button); b.pause(2000); let selector_date = supplier_selector + ' .checkout-table-supplier .checkout-date .datepicker table tr:nth-child(' + n_row + ') td:nth-child(' + n_col + ')'; b.click(selector_date); b.pause(2000); b.click(selector_date_button); b.pause(3000); let strDate = delivery_day + '.' + zeroBeforeMonth(current_date.getMonth()) + '.' + current_date.getFullYear(); b.element('css selector', 'body', function () { console.log('delivery date = ' + strDate); }); let selector_date_text = supplier_selector + ' .checkout-table-supplier .checkout-date strong'; b.waitForElementPresent(selector_date_text, delay, true, function () { // console.log('the button to set delivery date is here...'); }); b.verify.containsText(selector_date_text, strDate); b.pause(2000); }  function checkOrderCountByStatus(orderStatusSelector, statusName) { let orderStatusCount; browser.getText(orderStatusSelector, function (result) { orderStatusCount = parseInt(result.value); browser.elements('css selector', '.history-table tr[class="history-orders-item ng-star-inserted"]', function (result) { if (result.value.length === orderStatusCount) { console.log('Фильтрация по статусу "' + statusName + '" прошла успешно'); } else { console.log('Фильтрация по статусу "' + statusName + '" прошла не так как ожидалось'); } } ); }); if (orderStatusCount) { return orderStatusCount; } }  function makeOrderAndFinishIt() { let productSelector = 'app-product-list tr[app-product-entry]:nth-child(1)'; let addProductSelector = productSelector + ' td:nth-child(6) .quantity-plus'; let productTitleSelector = productSelector + ' td:nth-child(1)'; browser.getText(productTitleSelector, function (result) { let productName = result.value; let firstProductNameSelector = 'app-order app-item:nth-child(1) div.cart-item-title'; browser.click(addProductSelector); browser.pause(500); browser.expect.element(firstProductNameSelector).text.to.equal(productName); browser.pause(delay / 2); let buttonOrder = ".cart-buttons div:nth-child(2) a"; browser.pause(delay / 2); browser.click(buttonOrder); browser.pause(delay / 2); browser.saveScreenshot('tesssttformorderpage.png'); test_delivery_date(browser, '', 1, delay) browser.saveScreenshot('tesssttformorderpagedateset.png'); browser.click('.checkout-table-actions a:nth-child(2)'); browser.waitForElementVisible('.checkout-empty-content a[class="button"]', delay, false); browser.saveScreenshot('testformorderpagempty.png'); browser.url('https://dev.mixcart.ronasit.com/client/history', () => { console.log('Loading https://dev.mixcart.ronasit.com/client/history...');//Переходим на страницу истории заказов }); browser.pause(delay / 2); browser.saveScreenshot('historypage.png'); browser.getText('app-history-orders-statuses .completed', function (result) { let completedCount = parseInt(result.value); browser.click('.history-table tbody tr[class="history-orders-item ng-star-inserted"]:nth-child(1) td:nth-child(7) app-order-statuses-action-button:nth-child(2) a'); let newNumber = parseInt(String(completedCount+1)); let re = new RegExp(String(newNumber)); browser.expect.element('app-history-orders-statuses .completed').text.to.match(re).before(delay); browser.expect.element('.history-table tbody tr[class="history-orders-item ng-star-inserted"]:nth-child(1) td:nth-child(7) app-order-statuses-action-button:nth-child(2) a').text.to.equal('ПОВТОРИТЬ').before(delay); }); }); }  makeOrderAndFinishIt();   browser.pause(2000); browser.click("span[class='header-profile-name']"); browser.click("a[class='button-link header-user-logout']"); browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока не выйдем },

	'Test DEV-1920 order history - repeat order': function (browser) {

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
			console.log('Mo\'s appeared...');
		});

		browser.expect.element('.guest-form-fields a:nth-child(2)').text.to.match(/^Mo\'s/);

		browser.click('.guest-form-fields a:nth-child(2)');

		browser.waitForElementVisible('.header-profile .dropdown-trigger .header-profile-name', delay, function (result) {
			console.log('.header-profile-name appeared...');
		});

		browser.expect.element('.header-profile .dropdown-trigger .header-profile-name').text.to.equal('Mo\'s');

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

		function makeOrderAndFinishIt() {
			let productSelector = 'app-product-list tr[app-product-entry]:nth-child(1)';
			let addProductSelector = productSelector + ' td:nth-child(6) .quantity-plus';
			let productTitleSelector = productSelector + ' td:nth-child(1)';
			browser.getText(productTitleSelector, function (result) {
				let productName = result.value;
				let firstProductNameSelector = 'app-order app-item:nth-child(1) div.cart-item-title';
				browser.click(addProductSelector);
				browser.pause(500);
				browser.expect.element(firstProductNameSelector).text.to.equal(productName);
				browser.pause(delay / 2);
				let buttonOrder = ".cart-buttons div:nth-child(2) a";
				browser.pause(delay / 2);
				browser.click(buttonOrder);
				browser.pause(delay / 2);
				browser.saveScreenshot('tesssttformorderpage.png');
				test_delivery_date(browser, '', 1, delay)
				browser.saveScreenshot('tesssttformorderpagedateset.png');
				browser.click('.checkout-table-actions a:nth-child(2)');
				browser.waitForElementVisible('.checkout-empty-content a[class="button"]', delay, false);
				browser.saveScreenshot('testformorderpagempty.png');
				browser.url('https://dev.mixcart.ronasit.com/client/history', () => {
					console.log('Loading https://dev.mixcart.ronasit.com/client/history...');//Переходим на страницу истории
				                                                                           // заказов
				});
				browser.pause(delay / 2);
				browser.saveScreenshot('historypage.png');
				browser.getText('app-history-orders-statuses .completed', function (result) {
					let completedCount = parseInt(result.value);
					browser.click('.history-table tbody tr[class="history-orders-item ng-star-inserted"]:nth-child(1) td:nth-child(7) app-order-statuses-action-button:nth-child(2) a');
					let newNumber = parseInt(String(completedCount + 1));
					let re = new RegExp(String(newNumber));
					browser.expect.element('app-history-orders-statuses .completed').text.to.match(re).before(delay);
					browser.expect.element('.history-table tbody tr[class="history-orders-item ng-star-inserted"]:nth-child(1) td:nth-child(7) app-order-statuses-action-button:nth-child(2) a').text.to.equal('ПОВТОРИТЬ').before(delay);
					browser.click('.history-table tbody tr[class="history-orders-item ng-star-inserted"]:nth-child(1) td:nth-child(7) app-order-statuses-action-button:nth-child(2) a');
					browser.expect.element('.header-cart span span:nth-child(2)').text.to.equal('1').before(delay);
					browser.click('.header-cart');
					browser.pause(1000);
					browser.waitForElementPresent('.checkout-table-goods tbody tr:nth-child(1) .table-item', delay, false);
					browser.expect.element('.checkout-table-goods tbody tr:nth-child(1) .table-item').text.to.equal(productName).before(delay);
				});
			});
		}

		makeOrderAndFinishIt();

		browser.pause(2000);
		browser.click("span[class='header-profile-name']");
		browser.click("a[class='button-link header-user-logout']");
		browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока
	                                                                                                          // не
	                                                                                                          // выйдем
	},
};
