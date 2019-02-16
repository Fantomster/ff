module.exports = {
	before: function (browser) {
		console.log('Begining test DEV-1629...');
		console.log('Validation check Test...');
	},

	after: function (browser) {
		console.log('Finishing test DEV-1629...');
	},
	'Test dev.mixcart.ronasit.com/login': function (browser) {

		browser.url('http://dev.mixcart.ronasit.com/login', () => {
			console.log('Loading http://dev.mixcart.ronasit.com/login...');
		});
		browser.waitForElementVisible('#email', 3000, function () {
			console.log('email input appeared...');
		});
		browser.waitForElementVisible('#password', 3000, function () {
			console.log('password input appeared...');
		});
		browser.waitForElementVisible('.dropdown-trigger', 3000, function () {
			console.log('drop-down trigger appeared appeared...');
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

		browser.element('css selector', '#email', function () {
			browser.Keys.ENTER;
		});

		browser.pause(2000);
		browser.saveScreenshot('noemail.jpg');

		browser.assert.elementPresent("div[class='form-error']", 'Error message as expected!!!');

		browser.setValue('#email', 'bigle6732@');
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
