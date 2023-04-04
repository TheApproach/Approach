<?php

namespace Approach\Service;
 
enum format: int
{
    case json = 0;
    case xml = 1;
    case csv = 2;
    case list = 3;
    case indexed_tree = 4;
    case dictionary_tree = 5;
    case renderable = 6;
    case raw = 7;
    case binary = 8;
    case text = 9;
    case html = 10;
    case css = 11;
    case javascript = 12;
    case php = 13;
    case sql = 14;
    case markdown = 15;
    case yaml = 16;
    case ini = 17;
    case jsonschema = 24;
    case soap = 25;
    case wsdl = 26;
    case wadl = 27;
    case swagger = 28;
    case openapi = 29;
    case graphql = 30;
    case rest = 31;
    case rss = 32;
    case atom = 33;
    case rdf = 34;
    case mime = 35;
    case pdf = 36;
    case image = 37;
    case video = 38;
    case audio = 39;
    case archive = 40;
    case ask = 41;
    case default = 42;
}
