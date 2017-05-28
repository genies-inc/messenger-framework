const http = require('http');
const Url = require('url');

const handler = (req, res) => {
  const url = Url.parse(req.url, true);

  const request = {
    method : req.method,
    url : req.url,
    protocol : `HTTP/${req.httpVersion}`,
    query : url.query,
    pathname : url.pathname
  };

  let body_str = '';
  req.on('data', chunk => body_str += chunk);
  req.on('error', e => { console.error(e.message) });

  req.on('end', () => {
    let result = {
      request : request,
      header : req.headers
    };
    if (body_str) {
      result.body = body_str;
    }
    res.writeHead(200, {'Content-Type': 'text/plain'});
    res.end(JSON.stringify(result));
  });
}

const server = http.createServer(handler);
server.listen(process.env.PORT || 8080, process.env.HOST || 'localhost');
