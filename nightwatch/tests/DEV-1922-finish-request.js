let delay = 10000;
module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1922 finish request...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1922 finish request...');
		browser.end();
	},

	'Test DEV-1922 finish request': function (browser) {

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

		browser.refresh();

		browser.waitForElementPresent('.requests-list app-request-card:nth-child(1) .box .requests-item-title', delay);

		browser.getText('.requests-list app-request-card:nth-child(1) .box .requests-item-title', function (result) {
			let requestProduct = result.value;
			let product = requestProduct.substr(-12);
			this.verify.equal(product, 'test product');
			browser.click('.requests-list app-request-card:nth-child(1) .box .requests-item-actions a');
			browser.waitForElementPresent('.popup-form .popup-form-links a:nth-child(2)', delay);
			browser.click('.popup-form .popup-form-links a:nth-child(2)');
			browser.waitForElementNotPresent('.popup-form .popup-form-links a:nth-child(2)', delay, false);
			browser.saveScreenshot('destrotrequest.jpg');
			browser.expect.element('.requests-list app-request-card:nth-child(1) .box .requests-item-title').text.to.not.equal(result.value).before(delay);
			browser.execute(function () {
				let a = document.querySelector('.filters div:nth-child(2) input');
				a.click();
			}, []);
			browser.expect.element('.requests-list app-request-card:nth-child(1) .box .requests-item-title').text.to.equal(result.value).before(delay);
		});

		browser.click("span[class='header-profile-name']");
		browser.pause(500);
		browser.click("a[class='button-link header-user-logout']");
		browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').before(delay); //Ждём пока
	                                                                                                          // не
	                                                                                                          // выйдем
	},
};
