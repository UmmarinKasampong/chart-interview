<?php

// Your hierarchical data
$url = "https://new-e-service.spcc.co.th/Dashbord/Api/Area/Get_sunburst";

$json = file_get_contents($url);

$json_data = json_decode($json, true);


$root = array("name" => "งานคดี", "children" => array());
$c = 0;
foreach ($json_data["data"] as $entry) {
    if (!isset($entry["value"])) {
        // data in root and current data : งานคดี
        addToHierarchy($root, $entry);
    }
}

function addToHierarchy(&$node, $entry)
{
    if ($entry["parent"] !== "") {
        if ($entry["parent"] === "0.0") {
            $node["children"][] = [
                "name" => $entry["name"],
                "children" => [] // Children array can be empty initially
            ];
            addToHierarchyChild($node, $entry);
        };
    }
}


function addToHierarchyChild(&$root, $entry)
{
    global $json_data;
    foreach ($json_data["data"] as $childEntry) {
        if ($childEntry["parent"] === $entry["id"]) {
            foreach ($root["children"] as &$parentNode) {
                if ($parentNode["name"] === $entry["name"]) {
                    if (!isset($childEntry["color"])) {
                        $parentNode["children"][] = [
                            "name" => $childEntry["name"],
                            "value" => $childEntry["value"]
                        ];
                    } else {
                        $parentNode["children"][] = [
                            "name" => $childEntry["name"],
                            "value" => $childEntry["value"]
                        ];
                    }

                    // Recursively add children for this child node
                    addToHierarchyChild($parentNode, $childEntry);
                }
            }
        }
    }
}


// print_r($json_data['title'])

$json2 =  json_encode($root, JSON_PRETTY_PRINT);
// echo '<pre>' . $json2 . 's</pre>'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunburst Chart</title>

    <!-- Resources -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/hierarchy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <style>
        body {
            padding: 20px;
        }

        .header {
            text-align: center;
            font-size: 25px;

        }


        #chartdiv {
            width: 100%;
            height: 90vh;
            overflow: auto;
        }
    </style>

</head>

<body>
    <h1 class="header"><?php echo $json_data['title']; ?></h1>
    <div id="chartdiv"></div>

    <script>
        // Create root and chart
        var root = am5.Root.new("chartdiv");

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        var container = root.container.children.push(
            am5.Container.new(root, {
                width: am5.percent(100),
                height: am5.percent(100),
                layout: root.verticalLayout
            })
        );

        var series = container.children.push(
            am5hierarchy.Pack.new(root, {
                downDepth: 1,
                initialDepth: 1,
                valueField: "value",
                categoryField: "name",
                childDataField: "children"
            })
        );
        var data = []

        data.push(<?php echo $json2 ?>)
        series.data.setAll([data[0]]);
        series.set("selectedDataItem", series.dataItems[0]);

        container.children.unshift(
            am5hierarchy.BreadcrumbBar.new(root, {
                series: series
            })
        );
    </script>
</body>

</html>