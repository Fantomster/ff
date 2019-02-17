module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1629...');
		console.log('Validation check Test...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1629...');
	},
	'Test /login validation': function (browser) {

		browser.url(browser.globals.site_url + '/login', () => {
			// console.log('Loading http://dev.mixcart.ronasit.com/login...');
		});
		browser.waitForElementVisible('#email', 3000, function () {
			// console.log('email input appeared...');
		});
		browser.waitForElementVisible('#password', 3000, function () {
			// console.log('password input appeared...');
		});
		browser.waitForElementVisible('.dropdown-trigger', 3000, function () {
			// console.log('drop-down trigger appeared appeared...');
		});
		browser.click('.dropdown-trigger');
		browser.click('.dropdown-content a:nth-child(1)');
		// browser.saveScreenshot('screenshotstest1.png');

		browser.clearValue('#email', function () {
			console.log('Clearing email input!!!');
		});
		browser.clearValue('#password', function () {
			console.log('Clearing password input!!!');
		});

		browser.click('#email').moveTo(null, 0, -100).mouseButtonClick();
		// browser.element('css selector', '#email', function () {
		// 	browser.Keys.ENTER;
		// });

		browser.pause(3000);
		browser.saveScreenshot('noemail.jpg');

		browser.assert.elementPresent("div[class='form-error']", 'Email is required');
		browser.saveScreenshot('noemail1.jpg');

		browser.setValue('#email', 'bigle6732@');
		browser.saveScreenshot('noemail2.jpg');

		browser.element('css selector', '#email', function () {
			browser.Keys.ENTER;
		});
		browser.pause(2000);
		browser.saveScreenshot('wrongemail.jpg');
		browser.expect.element('#email').to.have.value.that.equals('bigle6732@', 'Typing wrong email address!!!');

		browser.assert.elementPresent("div[class='form-error']", 'Error message appeared as email is wrong!!!');

		browser.clearValue('#email', function () {
			console.log('Clearing email input!!!');
		});

		browser.setValue('#email', 'bigle6732@gmail.com');
		browser.element('css selector', '#email', function () {
			browser.Keys.ENTER;
		});
		browser.setValue('#password', 'QWEasd123');
		browser.expect.element('#email').to.have.value.that.equals('bigle6732@gmail.com', 'We have correct email');
		browser.expect.element('#password').to.have.value.that.equals('QWEasd123', 'We have correct password');

		browser.assert.elementNotPresent("div[class='form-error']", 'There is no error messages as expected!!!');

		browser.clearValue('#password', function () {
			console.log('Clearing password input!!!');
		});

		browser.pause(3000);
		browser.saveScreenshot('screenshotstest2.png');

		browser.end();

	}
};
