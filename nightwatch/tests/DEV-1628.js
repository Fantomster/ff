module.exports = {
    before : function(browser) {
        console.log('Begining test DEV-1628...');
        console.log('Athorization check Test...');
    },

    after : function(browser) {
        console.log('Finishing test DEV-1628...');
    },
    'Test /login': function(browser) {

        browser.url(browser.globals.site_url + '/login', () => {
            console.log('Loading '+browser.globals.site_url +'/login...');
        });
        browser.waitForElementVisible('#email', 3000, function() {
            console.log('email input appeared...');
        });
        browser.waitForElementVisible('#password', 3000, function() {
            console.log('password input appeared...');
        });
        browser.waitForElementVisible('.dropdown-trigger', 3000, function() {
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

        browser.setValue('#email', 'bigle6732@gmail.com'); //выставить в настройках среды в nightwatch.json по аналогии с site_url
        browser.setValue('#password', 'QWEasd123');
        browser.expect.element('#email').to.have.value.that.equals('bigle6732@gmail.com');
        browser.expect.element('#password').to.have.value.that.equals('QWEasd123');
        browser.submitForm('form');

        browser.expect.element('h3[class="guest-form-title"]').text.to.equal('Ваш бизнес-профиль').after(3000);

        browser.click('.guest-form-fields a:nth-child(3)');

        browser.waitForElementVisible('.header-profile-name', 3000, function(result) {
            console.log('.header-profile-name appeared...');
        });

        browser.expect.element("span[class='header-profile-name']").text.to.equal('Betsy');
        browser.click("span[class='header-profile-name']");
        browser.click("a[class='button-link header-user-logout']");
        browser.expect.element("h3[class='guest-form-title']").text.to.equal('Добро пожаловать').after(1500);

        // browser.saveScreenshot('screenshotstest2.png');

        browser.end();

    }
};