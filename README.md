# Satis Server Build Pack

This is a build pack that creates a valid [Satis](http://getcomposer.org/doc/articles/handling-private-packages-with-satis.md) server for the `satis.json` in the build directory, allowing you to serve your own private [Composer](http://getcomposer.org) repo.

## Usage

Place your `satis.json` file in your project root. The buildpack will find this in the top-level directory and serve them.

Configure your application to use this buildpack:

```
$ heroku config:add BUILDPACK_URL=https://github.com/rjocoleman/heroku-buildpack-satis-server.git
```

And push! Your Heroku app is now a Satis server. The index will be rebuilt on every deploy.
If you have trouble deploying or indexing please read _Github API limits_ below.

### GitHub API limits

Github API key can be set so that satis doesn't fail when pulling information from github.  
(Only [60 unauthenticated requests per hour are allowed](http://developer.github.com/v3/#rate-limiting))

```
$ heroku config:add GITHUB_API_KEY=zyx
```

When [creating an oAuth Token](https://help.github.com/articles/creating-an-oauth-token-for-command-line-use) I reccomend `repo` to allow access to private repos you can access or use no scope for public read-only access.

```
$ curl -u 'GITHUB_USERNAME' -d '{"scopes":["repo"],"note":"Satis (Heroku Indexer)"}' https://api.github.com/authorizations
``` 

It's also reccomended to have the [Heroku Lab: user-env-compile](https://devcenter.heroku.com/articles/labs-user-env-compile) enabled before you first deploy so when the first index is run your Github oAuth token is used:

```
$  heroku labs:enable user-env-compile
``` 


### Rebuilding the index

The command to rebuild is `satis build satis.json web/`
This can be used via: 

```
$ heroku run satis build satis.json web/
```

or Heroku's scheduler addon can be used to keep your index updated:

```
$ heroku addons:add scheduler:standard
```  
```
$ heroku addons:open scheduler:standard
```


## Warning!

This buildpack uses PHP 5.4's inbult webserver, i.e. `php -s`, to serve your files. There is no authentication or protection.

Therefore, corporate secrets and other such private things should not be put into this satis server.

If you choose to vendor in a version of PHP that does not include the webserver then nothing will work.


## Contributing

1. Fork it.
2. Create your feature branch (`git checkout -b my-new-feature`).
3. Commit your changes (`git commit -am 'Add some feature'`).
4. Push to the branch (`git push origin my-new-feature`).
5. Create new Pull Request.
6. Explain why you think this belongs in the code here, and not inside your own gem that requires this one.

Pull requests most graciously accepted.


## Hacking

To use this buildpack, fork it on Github.  Push up changes to your fork, then create a test app with `--buildpack <your-github-url>` and push to it.

To change the vendored binaries for PHP, use the helper scripts in the `support/` subdirectory.  You'll need an S3-enabled AWS account and a bucket to store your binaries in.

For example, you can change the default version of PHP to v5.4.10.

Install Heroku's [vulcan gem](https://github.com/heroku/vulcan), then:

`$ vulcan create you-vulcan-platform-name`  
This will create a cedar platform, so the output will look familiar to heroku users.

`$ export AWS_ID=xxx AWS_SECRET=yyy S3_BUCKET=zzz`  
`$ s3 create $S3_BUCKET`  
`$ support/package_php 5.4.10`  

Open `bin/compile` in your editor, and change the following lines:

`DEFAULT_PHP_VERSION="5.4.10"`  
`PHP_S3_BUCKET=zzz`  

Commit and push the changes to your buildpack to your Github fork, then push your sample app to Heroku to test. You should see:
````
    -----> Vendoring PHP (v5.4.10)
```