# AMZ Shell for Selling Partner API
## Usage
```
Usage: php amz-shell.php [OPTIONS]
--xml XML_FILE          set the xml file to upload
        --cred CREDENTIALS      set the file with credentials
        --content_type TYPE     set the content type of the file DEFAULT: text/xml; charset=utf-8
        --report DOC_ID         get xml report
        -marketplaces "ID1 ID2" set marketplace in quotes and space between: -m "AV2FSG2 AGHWE3R5"
        -t TYPE                 set the feed type
        -g FEED_ID              get feed data
        -l FEED_TYPE            list feeds type
        -v                      set verbose
```
### Uploading a xml file
` php amz-shell.php --xml <path_to_xml_file> --cred <.env or .json> --t <feed_type> --marketplaces '<marketId_1> <marcketId_2>"`

*Path to the xml file* can be absolute or relative.

The credential file is like:
```
.env file
------------------------------------------------------------
refresh_token=Atzr|...
region=eu-west-1
access_key=access
secret_key=secret
endpoint=https://sellingpartnerapi-eu.amazon.com
role_arn=arn:role
```

```
JSON file
------------------------------------------------------------
{
    "refresh_token" : "Atzr|...",
    "region": "eu-west-1',
    "access_key": "access",
    "secret_key": "secret",
    "endpoint": "https://sellingpartnerapi-eu.amazon.com",
    "role_arn": "role"
}
```
Can read both type of files.

### MarketplacesId
When you try to upload a XML document, you need to set the market where 
the product is, in Europe are more than one market so it required to set 
the marketplace id.

For this work we have the `--marketplaces` options, the value of this options is a string whit the ids inside and there will be space between ids.

```
--marketplaces "id1 id2 id3 ... idn"
```
