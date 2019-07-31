const PuppeteerApiClient = require(__dirname + '/PuppeteerApiClient');

const options = JSON.parse(process.argv[2]);

function guardOptions(options)
{
  if(options.uri === undefined) {
    throw new Error('Options must have URI.');
  }
}

// todo: Get transfer stats
// todo: pass puppeteer launch options from the JSON command
// node src/browser.js '{"uri":"http:\/\/localhost:8880\/javascript\/"}'
// node src/browser.js '{"uri":"http:\/\/localhost:8880\/javascript\/","options":{"screenshots":"\/application\/build\/screenshots"}}'

(async () => {

  guardOptions(options);

  let client = new PuppeteerApiClient(options);

  let result = await client.processRequest(options.uri);

  console.log(JSON.stringify(result));

})();