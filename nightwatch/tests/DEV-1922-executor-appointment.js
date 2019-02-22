let delay = 10000;

let supRequestName = 'Поставщик алкоголя';
let supRequestPrice = '1000';
let supRequestComment = 'test';

module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1922 executor appointment...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1922 executor appointment...');
		browser.end();
	},

	'Test DEV-1922 executor appointment': function (browser) {

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
		browser.pause(200);
		browser.click('.dropdown-content a:nth-child(1)');
		browser.pause(200);

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

		browser.resizeWindow(1980, 1080);

		browser.url('https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods', () => {
			console.log('Loading https://dev.mixcart.ronasit.com/client/create-order/my-supplier-goods...');//
		});

		let requestLinkSelector = '.order .tabs a:nth-child(3)';
		browser.waitForElementVisible(requestLinkSelector, delay);
		browser.click(requestLinkSelector);

		let createRequestButtonSelector = 'app-requests button';
		browser.waitForElementVisible(createRequestButtonSelector, delay);

		browser.click(createRequestButtonSelector);

		let createRequestFormSelector = 'app-create-request-modal .createrequest form';
		let createRequestFormLinkSelector = createRequestFormSelector + ' .form-buttons a';
		browser.waitForElementVisible(createRequestFormLinkSelector, delay);

		let requestFormRowOne = createRequestFormSelector + ' .row:nth-child(2)';
		let productName = requestFormRowOne + ' .col:nth-child(1) input';
		let requestFormRowTwo = createRequestFormSelector + ' .row:nth-child(3)';
		let productVolume = requestFormRowTwo + ' .col:nth-child(1) input';
		browser.setValue(productName, ['test product', browser.Keys.TAB, browser.Keys.ARROW_DOWN], function () {
		});
		browser.setValue(productVolume, [
			'1000',
			browser.Keys.TAB,
			browser.Keys.ARROW_DOWN,
			browser.Keys.TAB,
			browser.Keys.ARROW_DOWN,
			browser.Keys.ARROW_DOWN,
			browser.Keys.TAB,
			'2',
			browser.Keys.TAB,
			'test'
		], function () {
		});
		browser.pause(200);
		browser.click(createRequestFormLinkSelector);
		browser.waitForElementNotPresent(createRequestFormSelector, delay);

		browser.pause(200);

		browser.pause(200);

		browser.refresh();

		browser.waitForElementPresent('.requests-list app-request-card:nth-child(1) .box .requests-item-title', delay);

		browser.getText('.requests-list app-request-card:nth-child(1) .box .requests-item-title', function (result) {
			console.log(result);
			let requestProduct = result.value;
			let product = requestProduct.substr(-12);
			this.verify.equal(product, 'test product');
		});

		browser.execute(function () {
			window.open('https://dev.mixcart.ru/login', '_blank');
		}, []);
		browser.windowHandles(function (result) {
			console.log(result);
			browser.switchWindow(result.value[1]);
		});
		browser.pause(3000);
		browser.resizeWindow(1980, 1080);

		browser.setValue('#loginform-email', 'mixcart@list.ru');
		browser.pause(200);
		browser.setValue('#loginform-password', 'max999');
		browser.pause(200);

		browser.submitForm('#login-form');

		browser.waitForElementPresent('.btn-continue', delay);

		browser.click('.btn-continue');

		browser.waitForElementPresent('.sidebar li:nth-child(6) a', delay);

		browser.click('.sidebar li:nth-child(6) a');

		browser.waitForElementPresent('.content .row>.col-xs-12:nth-child(2)', delay);

		let requestNameSelector = '.content .row>.col-xs-12:nth-child(2) .list-wrapper>div:nth-child(1) .req-items .req-name';

		browser.getText(requestNameSelector, function (result) {
			console.log(result);
			let name = result.value;
			browser.windowHandles(function (result) {
				console.log(result);
				browser.switchWindow(result.value[0]);
			});
			browser.saveScreenshot('firstwindow.jpg');
			browser.getText('.requests-list app-request-card:nth-child(1) .box .requests-item-title', function (result) {
				console.log(result);
				this.verify.equal(name, result.value);
			});
			browser.windowHandles(function (result) {
				console.log(result);
				browser.switchWindow(result.value[1]);
			});
			browser.saveScreenshot('anotherwindow.jpg');
		});

		browser.click(requestNameSelector);
		browser.waitForElementPresent('.box-body button.callback', delay);
		browser.click('.box-body button.callback');
		browser.waitForElementPresent('.swal2-buttonswrapper button:nth-child(1)', delay);
		browser.setValue('.swal2-input', [supRequestPrice, browser.Keys.ENTER]);
		browser.waitForElementPresent('.swal2-textarea', delay);
		browser.setValue('.swal2-textarea', [supRequestComment]);
		browser.click('.swal2-buttonswrapper button:nth-child(1)');
		browser.waitForElementPresent('.swal2-success .swal2-success-ring', delay);
		browser.saveScreenshot('newwindow.jpg');

		browser.windowHandles(function (result) {
			console.log(result);
			browser.switchWindow(result.value[0]);
		});

		let viewNewRequestSelector = '.requests-list app-request-card .requests-item:nth-child(1) .requests-item-actions a:nth-child(2)';
		browser.click(viewNewRequestSelector);

		browser.pause(2000);

		let requestSuppliersTableSelector = '.request-suppliers tbody tr:nth-child(1)';

		let supplierName = requestSuppliersTableSelector + ' .table-supplier';
		let supplierPrice = requestSuppliersTableSelector + ' .table-price';
		let supplierComment = requestSuppliersTableSelector + ' .table-comment';
		let supplierAppoint = requestSuppliersTableSelector + ' .table-actions a';

		browser.waitForElementPresent(requestSuppliersTableSelector, delay);

		browser.getText(supplierName, function (result) {
			this.verify.equal(result.value, supRequestName);
		});
		browser.getText(supplierPrice, function (result) {
			this.verify.equal(result.value, supRequestPrice);
		});
		browser.getText(supplierComment, function (result) {
			this.verify.equal(result.value, supRequestComment);
		});

		browser.click(supplierAppoint);

		browser.waitForElementPresent('.popup-form .popup-form-links a:nth-child(2)', delay);

		browser.click('.popup-form .popup-form-links a:nth-child(2)');

		browser.waitForElementNotPresent('.popup-form', delay);

		browser.expect.element(supplierAppoint).to.have.attribute('class').which.equals('button button-red').before(delay);
		browser.expect.element(supplierAppoint).text.to.equals('СНЯТЬ').before(delay);

		browser.saveScreenshot('requestcontent.jpg');

		browser.click("span[class='header-profile-name']");
		browser.pause(500);
		browser.click("a[class='button-link header-user-logout']");
		browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока
	                                                                                                          // не
	                                                                                                          // выйдем
	},
};
