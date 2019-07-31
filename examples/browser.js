const puppeteer = require('puppeteer');

const request = JSON.parse(process.argv[2]);

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();

  let response = await page.goto(request.uri);

  let headers = response.headers();

  console.log({'headers': headers});

  let content = await response.json();

  console.log(content);

  await browser.close();
})();