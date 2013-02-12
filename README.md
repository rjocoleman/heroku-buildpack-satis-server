# Satis Server Build Pack

This is a Heroku build pack that generates a valid [Satis](http://getcomposer.org/doc/articles/handling-private-packages-with-satis.md) server for the `satis.json` in the build directory, allowing you to serve your own private [Composer](http://getcomposer.org) repo. The generated Satis `index.html` and `packages.json` are uploaded to Amazon S3 for serving.


## Usage

Place your `satis.json` file in your project root.

Configure your application to use this buildpack:

```
$ heroku config:add BUILDPACK_URL=https://github.com/rjocoleman/heroku-buildpack-satis-server.git
```  
```
$ heroku config:add AWS_ACCESS_KEY_ID=123
```  
```
$ heroku config:add AWS_SECRET_ACCESS_KEY=456
```  
```
$ heroku config:add S3_BUCKET=example-satis
```

And push! Your Heroku app is now a Satis server. The index will be rebuilt on every deploy.
If you have trouble deploying or indexing please read _[Github API limits](#github-api-limits)_ below.


### Operating modes

This buildpack operates in three modes:

#### Stream

Satis are retrived from S3 via SSL and streamed to the client via Heroku. This is the default mode, it allows for the Heroku feature set i.e. `ssl` and `domains`.  
It's the simplest configuration mode as it requires no settings. Point `composer.json` at your Heroku app's url and you're away.  
The disadvantage of this is performance; there is no caching so it can be slow for larger files.

#### Redirect

Accessing your Heroku app's web url will redirect (303) the visitor to the files hosted on Amazon S3 (HTTPS).  
Composer will follow this redirect.
This assumes that the files hosted on S3 have ACLs to allow them to be publicly accessed which is the setting they are uploaded with.  
To enable this:
```
$ heroku config:add MODE=redirect
```

#### External

This mode is for using Amazon Cloudfront or CNAME with S3 etc. This decouples your Heroku app's web url and the file hosting completely.  
Visitors that access your Heroku web url will be redirected (301) to the FDQN that you specify.
To enable this, you need both of the following:
```
$ heroku config:add MODE=external
```  
```
$ heroku config:add SATIS_URL=http://satis.example.com
```


### GitHub API limits

GitHub API key can be set so that satis doesn't fail when pulling information from GitHub.  
(Only [60 unauthenticated requests per hour are allowed](http://developer.github.com/v3/#rate-limiting))

```
$ heroku config:add GITHUB_API_KEY=zyx
```

When [creating an oAuth Token](https://help.github.com/articles/creating-an-oauth-token-for-command-line-use) I reccomend `repo` to allow access to private repos you can access or use no scope for public read-only access.

```
$ curl -u 'GITHUB_USERNAME' -d '{"scopes":["repo"],"note":"Satis (Heroku Indexer)"}' https://api.github.com/authorizations
``` 

It's also reccomended to have the [Heroku Lab: user-env-compile](https://devcenter.heroku.com/articles/labs-user-env-compile) enabled before you first deploy so when the first index is run your GitHub oAuth token is used:

```
$  heroku labs:enable user-env-compile
``` 


### Rebuilding the index

The command to regenerate is `rebuild`
This can be used via: 

```
$ heroku run rebuild
```

or Heroku's scheduler addon can be used to keep your index updated:

```
$ heroku addons:add scheduler:standard
```  
```
$ heroku addons:open scheduler:standard
```


## Warning

Your satis `index.html` and `packages.json` are uploaded to Amazon S3 as world readable. There is no authentication or access control. Packages in private repos won't be accessable but this could constitute information leakage.


## Contributing

1. Fork it.
2. Create your feature branch (`git checkout -b my-new-feature`).
3. Commit your changes (`git commit -am 'Add some feature'`).
4. Push to the branch (`git push origin my-new-feature`).
5. Create new Pull Request.
6. Explain why you think this belongs in the code here, and not inside your own gem that requires this one.

Pull requests most graciously accepted.


## Hacking

To use this buildpack, fork it on Github. Push up changes to your fork, then create a test app with `--buildpack <your-github-url>` and push to it.

To change the vendored binaries for PHP, use the helper scripts in the `support/` subdirectory.  You'll need an S3-enabled AWS account and a bucket to store your binaries in, this is distinct from your S3 storage mentioned above that is used for hosting the generated satis files.

For example, you can change the default version of PHP to v5.4.10.

Install Heroku's [vulcan gem](https://github.com/heroku/vulcan), then:

`$ vulcan create you-vulcan-platform-name`  
This will create a cedar platform so the output will look familiar to heroku users.

`$ export AWS_ID=xxx AWS_SECRET=yyy S3_BUCKET=zzz`  
`$ bash ./support/aws/s3 create $S3_BUCKET`  
`$ bash ./support/package_php 5.4.10`  

Open `bin/compile` in your editor, and change the following lines:

`DEFAULT_PHP_VERSION="5.4.10"`  
`PHP_S3_BUCKET=zzz`  

Commit and push the changes to your buildpack to your Github fork, then push your sample app to Heroku to test. You should see:
````
    -----> Vendoring PHP (v5.4.10)
```