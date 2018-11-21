const puppeteer = require('puppeteer');

const request = JSON.parse(process.argv[2]);

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();
  await page.goto(request.uri);
  let content = await page.content();
  console.log(content);

  await browser.close();
})();