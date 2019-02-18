module.exports = {
	// before: function (browser) {
	// 	console.log('Begining test DEV-1628...');
	// 	console.log('Athorization check Test...');
	// },
	// after: function (browser) {
	// 	console.log('Finishing test DEV-1628...');
	// },
	'Test /login': function (browser) {

		browser.url(browser.globals.site_url + '/login', () => {
				console.log('Loading ' + browser.globals.site_url + '/login...');
			})
			.waitForElementVisible('.button-login', browser.globals.angularTimeout, function () {
				console.log('email input appeared...');
			})
			.waitForElementVisible('#password', browser.globals.angularTimeout, function () {
				// console.log('password input appeared...');
			})
			.waitForElementVisible('.dropdown-trigger', browser.globals.angularTimeout, function () {
				// console.log('drop-down trigger appeared appeared...');
			})
			.click('.dropdown-trigger')
			.click('.dropdown-content a:nth-child(1)');

		browser.clearValue('#email', function () {
			// console.log('Clearing email input!!!');
		});
		browser.clearValue('#password', function () {
			// console.log('Clearing password input!!!');
		});

		browser
			.setValue('#email', browser.globals.credentials.email)
			.setValue('#password', browser.globals.credentials.password);
		browser.expect.element('#email').to.have.value.that.equals(browser.globals.credentials.email);
		browser.expect.element('#password').to.have.value.that.equals(browser.globals.credentials.password);
		browser.expect.element('h3[class="guest-form-title"]').text.to.equal(browser.globals.dev_1628.test3).after(3000);
		browser.submitForm('form');
		browser.pause(10000);
		browser.click('.guest-form-fields a:nth-child(3)');
		browser.waitForElementVisible('.header-profile-name', browser.globals.angularTimeout, function (result) {
			console.log('.header-profile-name appeared...');
		});
		browser.expect.element("span[class='header-profile-name']").text.to.equal(browser.globals.dev_1628.test2).after(3000);
		browser.click("span[class='header-profile-name']")
			.click("a[class='button-link header-user-logout']")
			.expect.element("h3[class='guest-form-title']").text.to.equal(browser.globals.dev_1628.test3).after(1500);
		// browser.saveScreenshot('screenshotstest4.png');
		browser.end();
	}
};
