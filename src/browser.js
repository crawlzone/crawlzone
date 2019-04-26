const puppeteer = require('puppeteer');

const request = JSON.parse(process.argv[2]);


// todo: Get headers
// todo: Get transfer stats
// todo: wrap response into JSON
// todo: pass puppeteer launch options from the JSON command
(async () => {
  const browser = await puppeteer.launch(
    {
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        // debug logging
        '--enable-logging', '--v=1'
      ]
    }
  );
  const page = await browser.newPage();
  await page.goto(request.uri);
  console.log(request.uri);
  let content = await page.content();
  console.log(content);

  await browser.close();
})();