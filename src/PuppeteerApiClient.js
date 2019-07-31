const Puppeteer = require('puppeteer');

module.exports = class {

  constructor(config) {
    this._config = {
      "options" : {
        "screenshots": null
      }
    };
    this._config = {...this._config, ...config};
  }

  async processRequest(uri) {
    const browser = await Puppeteer.launch({
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        // debug logging
        '--enable-logging', '--v=1'
      ]
    });

    const page = await browser.newPage();

    let response = await page.goto(uri);

    let headers = response.headers();

    let content = await page.content();

    if(this._config.options.screenshots) {
      let saveScreenshotsIn = this._config.options.screenshots;
      saveScreenshotsIn = saveScreenshotsIn.replace(/\/$/g, '');
      let path = saveScreenshotsIn + '/' + uri.replace(/\//g,'-') + '.png';
      await page.screenshot({path: path});
    }

    await browser.close();

    return {"response": {
      "url": response.url(),
      "headers": headers,
      "content": content,
      "status": response.status(),
    }};
  }

};