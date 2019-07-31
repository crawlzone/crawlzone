const {TestRunner, Reporter, Matchers} = require('@pptr/testrunner');
const PuppeteerApiClient = require('../../PuppeteerApiClient');
const fs = require('fs');

// Runner holds and runs all the tests
const runner = new TestRunner({
  parallel: 2, // run 2 parallel threads
  timeout: 1000, // setup timeout of 1 second per test
});
// Simple expect-like matchers
const {expect} = new Matchers();

// Extract jasmine-like DSL into the global namespace
const {describe, xdescribe, fdescribe} = runner;
const {it, fit, xit} = runner;
const {beforeAll, beforeEach, afterAll, afterEach} = runner;

// Test hooks can be async.
beforeAll(async state => {
  state.parallelIndex; // either 0 or 1 in this example, depending on the executing thread
});

describe('Testing Puppeteer API Client', () => {
  it('Testing Puppeteer API Client', async (state, test) => {
    let config = {};
    let client = new PuppeteerApiClient(config);

    let result = await client.processRequest('http://site1.local/');

    expect(result.response.status).toEqual(200);
    expect(result.response.url).toEqual('http://site1.local/');
    expect(result.response.headers.host).toEqual('site1.local');
    expect(result.response.headers.connection).toEqual('close');
    expect(result.response.content).toEqual(
      '<html><head></head><body><h1>Site1 Home Page</h1>\n' +
      '\n' +
      '<a href="customers.html">Customers</a><br>\n' +
      '<a href="http://site2.local">Our Partners</a></body></html>'
    );
  });

  it('Testing Puppeteer API Client Screenshots', async (state, test) => {

    let config = {
      "options" : {
        "screenshots": "/application"
      }
    };

    let client = new PuppeteerApiClient(config);

    let result = await client.processRequest('http://site1.local/');

    expect(fs.existsSync('/application/http:--site1.local-.png')).toBeTruthy();

    fs.unlinkSync("/application/http:--site1.local-.png");

  });
});

// Reporter subscribes to TestRunner events and displays information in terminal
const reporter = new Reporter(runner);

// Run all tests.
runner.run();