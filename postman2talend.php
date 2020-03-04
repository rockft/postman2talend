<?php
$postmanFile = ($argv[1])?$argv[1]:'postman.json';
$postmanString = file_get_contents(__DIR__.'/'.$postmanFile);
$postmanJson = json_decode($postmanString);

//var_dump($postmanJson);

$talendJson = json_decode(
'{
    "version": 6,
    "entities": [
        {
            "entity": {
                "type": "Project",
                "id": "d16782bf-c458-4f91-96fb-d1f96f5a758a",
                "name": "postman project"
            },
            "children": []
        }
    ]
}
');

//$talendJson->entities[0]->entity->id = uuid('postman project'.$postmanJson->name);
$talendJson->entities[0]->entity->id = $postmanJson->id;
$talendJson->entities[0]->entity->name = $postmanJson->name;

$entityTemplate = '{
    "entity": {
        "type": "Request",
        "method": {
            "requestBody": true,
            "link": "http://tools.ietf.org/html/rfc7231#section-4.3.3",
            "name": "POST"
        },
        "body": {
            "formBody": {
                "overrideContentType": true,
                "encoding": "application/x-www-form-urlencoded",
                "items": []
            },
            "bodyType": "Text"
        },
        "uri": {
            "query": {
                "delimiter": "&",
                "items": []
            },
            "scheme": {
                "name": "http",
                "version": "V11"
            },
            "host": "127.0.0.1",
            "path": "/path"
        },
        "id": "eb704a00-496f-496a-9285-941ce78e13d6",
        "name": "template",
        "description": "",
        "headers": []
    }
}';

$itemTemplate = '{
    "enabled": true,
    "encoded": true,
    "name": "",
    "value": ""
}';

$headerTemplate = '{
    "enabled": true,
    "name": "",
    "value": ""
}';

foreach ($postmanJson->requests as $pmItem) {
    $entity = json_decode($entityTemplate);
    $entity->entity->id = $pmItem->id;
    $entity->entity->name = $pmItem->name;
    $entity->entity->description = $pmItem->description;
    $entity->entity->method->name = $pmItem->method;

    // url parse
    $url = parse_url($pmItem->url);
    $entity->entity->uri->scheme->name = $url['scheme'];
    $entity->entity->uri->host = $url['host'];
    $entity->entity->uri->path = $url['path'];

    // url params
    $rawParameters2obj = function ($str) use ($itemTemplate)
    {
        $urlParams = explode('&', $str);
        $items = [];
        foreach ($urlParams as $urlParam) {
            $item = json_decode($itemTemplate);
            $urlParam = explode('=', $urlParam);
            $item->name = $urlParam[0];
            $item->value = $urlParam[1];
            $items[] = $item;
        }
        return $items;
    };
    if (!empty($url['query'])) {
        $entity->entity->uri->query->items = array_merge($entity->entity->uri->query->items, $rawParameters2obj($url['query']));
    }
    if ($pmItem->dataMode == 'raw') {
        $entity->entity->uri->query->items = array_merge($entity->entity->uri->query->items, $rawParameters2obj($pmItem->data));
    } else {
        foreach ($pmItem->data as $param) {
            $item = json_decode($itemTemplate);
            $item->name = $param->key;
            $item->value = $param->value;
            $entity->entity->uri->query->items[] = $item;
        }
    }

    // headers
    $rawHeaders2obj = function ($str) use ($itemTemplate)
    {
        $items = [];
        $pmHeaders = explode("\n", trim($str));
        foreach ($pmHeaders as $pmHeader) {
            $header = json_decode($headerTemplate);
            $pmHeader = explode(': ', $pmHeader);
            $header->name = $pmHeader[0];
            $header->value = $pmHeader[1];
            $items[] = $item;
        }
        
        return $items;
    };
    if (!empty($pmItem->headers))
    {
        $entity->entity->headers = array_merge($entity->entity->headers, $rawHeaders2obj($pmItem->headers));
    }

    //
    $talendJson->entities[0]->children[] = $entity;
}

file_put_contents(__DIR__.'/postman2talend.json', json_encode($talendJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo 'success';

/******************************************************************************** */

function uuid($str)
{
    $str = md5($str);
    for ($i=8; $i < 24; $i=$i+5) { 
        $str = substr_replace($str,'-',$i,0);
    }
    return $str;
    //return substr_replace(substr_replace(substr_replace(substr_replace(md5($str),'-',8,0),'-',13,0),'-',18,0),'-',23,0);
}
