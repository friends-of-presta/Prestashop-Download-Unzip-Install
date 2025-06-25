<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Téléchargeur de Versions PrestaShop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 600px;
            margin: 0 auto;
        }
        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .info-box {
            background-color: #f2f2f2; 
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            text-align: center;
        }
        .source-info {
            background-color: #e7f3ff;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            font-size: 0.9em;
        }
        .extensions ul {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            list-style: none;
            padding: 0;
        }
        .extensions li {
            flex-basis: 50%;
        }
        .icon-ok {
            color: green;
        }
        .icon-not-ok {
            color: red;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8em;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0 0 15px 0;
            font-size: 1.8em;
            font-weight: 300;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            line-height: 1.2;
        }
        .header .subtitle {
            font-size: 1.1em;
            opacity: 0.9;
            line-height: 1.5;
            max-width: 600px;
            margin: 0 auto;
        }
        .header .features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .header .feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95em;
            opacity: 0.9;
        }
        .header .feature-icon {
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 PrestaShop Download | Unzip | Install</h1>
            <div class="subtitle">
                Script automatisé pour faciliter le téléchargement et l'installation des différentes versions de PrestaShop depuis les dépôts GitHub officiels
            </div>
        </div>
        
        <?php
        function getRepositoryInfo($version) {
            // Détermine le dépôt à utiliser selon la version
            $versionNumber = floatval(str_replace(['v', '.0', '.1', '.2', '.3', '.4', '.5', '.6', '.7', '.8', '.9'], ['', '', '', '', '', '', '', '', '', '', ''], $version));
            
            if ($versionNumber >= 9.0) {
                return [
                    'repo' => 'jbromain/prestashop-community',
                    'api_url' => 'https://api.github.com/repos/jbromain/prestashop-community/tags',
                    'download_base' => 'https://github.com/jbromain/prestashop-community/releases/download/'
                ];
            } else {
                return [
                    'repo' => 'PrestaShop/PrestaShop',
                    'api_url' => 'https://api.github.com/repos/PrestaShop/PrestaShop/tags',
                    'download_base' => 'https://github.com/PrestaShop/PrestaShop/releases/download/'
                ];
            }
        }

        function getAllPrestaShopVersions() {
            $allVersions = [];
            
            // Récupérer les versions depuis le dépôt officiel (< v9)
            $officialRepo = [
                'repo' => 'PrestaShop/PrestaShop',
                'api_url' => 'https://api.github.com/repos/PrestaShop/PrestaShop/tags'
            ];
            
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: PrestaShop-Downloader"
                ]
            ];
            $context = stream_context_create($opts);
            
            // Récupération depuis le dépôt officiel
            $json = @file_get_contents($officialRepo['api_url'], false, $context);
            if ($json) {
                $tags = json_decode($json, true);
                foreach ($tags as $tag) {
                    $allVersions[$tag['name']] = [
                        'version' => $tag['name'],
                        'repo' => $officialRepo['repo']
                    ];
                }
            }
            
            // Récupérer les versions depuis le dépôt communautaire (>= v9)
            $communityRepo = [
                'repo' => 'jbromain/prestashop-community',
                'api_url' => 'https://api.github.com/repos/jbromain/prestashop-community/tags'
            ];
            
            $json = @file_get_contents($communityRepo['api_url'], false, $context);
            if ($json) {
                $tags = json_decode($json, true);
                foreach ($tags as $tag) {
                    $allVersions[$tag['name']] = [
                        'version' => $tag['name'],
                        'repo' => $communityRepo['repo']
                    ];
                }
            }
            
            // Trier les versions (les plus récentes en premier)
            uksort($allVersions, function($a, $b) {
                return version_compare($b, $a);
            });
            
            return $allVersions;
        }

        function downloadPrestaShop($version) {
            $repoInfo = getRepositoryInfo($version);
            
            // Format du nom de fichier selon le dépôt
            if ($repoInfo['repo'] === 'jbromain/prestashop-community') {
                $zipFile = "prestashop_{$version}.zip";
                $url = $repoInfo['download_base'] . "{$version}/prestashop_{$version}.zip";
            } else {
                $zipFile = "prestashop_{$version}.zip";
                $url = $repoInfo['download_base'] . "{$version}/prestashop_{$version}.zip";
            }
            
            echo "<div class='source-info'>";
            echo "<strong>Téléchargement depuis :</strong> " . $repoInfo['repo'] . "<br>";
            echo "<strong>URL :</strong> " . htmlspecialchars($url);
            echo "</div>";
            
            $fileContent = @file_get_contents($url);
            if ($fileContent === false) {
                echo "<div style='color: red;'>Erreur : Impossible de télécharger depuis cette URL. Vérifiez que la version existe.</div>";
                return false;
            }
            
            file_put_contents($zipFile, $fileContent);
            return $zipFile;
        }

        function unzipPrestaShop($zipFile) {
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo('.');
                $zip->close();
                unlink($zipFile);
                echo "Décompression de {$zipFile} réussie.<br>";
                echo "<form method='post'>";
                echo "Voulez-vous démarrer l'installation de PrestaShop maintenant ?";
                echo "<input type='hidden' name='redirect' value='index.php'>";
                echo "<input type='submit' value='OUI' name='reponse'>";
                echo "<input type='submit' value='NON' name='reponse'>";
                echo "</form>";
            } else {
                echo "Échec de la décompression de {$zipFile}<br>";
            }
        }

        function endScript() {
            echo "Fin du script";
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['version']) && empty($_POST['unzip']) && empty($_POST['redirect'])) {
                $zipFile = downloadPrestaShop($_POST['version']);
                if ($zipFile) {
                    echo "<div>Téléchargement de {$zipFile} terminé.</div>";
                    echo "<div>Voulez-vous décompresser le fichier téléchargé ?</div>";
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='version' value='{$_POST['version']}'>";
                    echo "<input type='hidden' name='unzip' value='{$zipFile}'>";
                    echo "<input type='submit' style='background-color: green; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;' value='OUI' name='reponse'>";
                    echo "<input type='submit' style='background-color: red; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;' value='NON' name='reponse'>";
                    echo "</form>";
                }
            } elseif (!empty($_POST['unzip']) && empty($_POST['redirect'])) {
                if ($_POST['reponse'] == "OUI") {
                    unzipPrestaShop($_POST['unzip']);
                } elseif ($_POST['reponse'] == "NON") {
                    endScript();
                }
            } elseif (!empty($_POST['redirect'])) {
                if ($_POST['reponse'] == "OUI") {
                    header('Location: ' . $_POST['redirect']);
                    endScript();
                } elseif ($_POST['reponse'] == "NON") {
                    endScript();
                }
            }
        } else {
            $allVersions = getAllPrestaShopVersions();
            echo "<div class='version-selector'>";
            echo "<h3>🎯 Quelle version souhaitez-vous installer ?</h3>";
            echo "<form method='post'>";
            echo "<select name='version'>";
            foreach ($allVersions as $versionData) {
                $repoLabel = ($versionData['repo'] === 'jbromain/prestashop-community') ? ' (Community)' : ' (Official)';
                echo "<option value='{$versionData['version']}'>{$versionData['version']}{$repoLabel}</option>";
            }
            echo "</select>";
            echo "<input type='submit' value='Télécharger'>";
            echo "</form>";
            echo "</div>";
            
            echo "<div class='source-info'>";
            echo "<strong>Sources utilisées :</strong><br>";
            echo "• Versions < 9.0 : <a href='https://github.com/PrestaShop/PrestaShop' target='_blank'>PrestaShop/PrestaShop</a> (Officiel)<br>";
            echo "• Versions ≥ 9.0 : <a href='https://github.com/jbromain/prestashop-community' target='_blank'>jbromain/prestashop-community</a> (Community)";
            echo "</div>";
        }
        ?>
        
        <p>
            <center>
                <b>ATTENTION</b>:<br>
            </center>
            La version de votre PHP n'est peut-être pas compatible avec la version Prestashop que vous allez installer.<br>
            <br>
            <a href="https://devdocs.prestashop-project.org/9/basics/installation/system-requirements/" target="_blank">Vérifiez ici les prérequis pour la version 9.x</a><br>
            <a href="https://devdocs.prestashop-project.org/8/basics/installation/system-requirements/" target="_blank">Vérifiez ici les prérequis pour la version 8.x</a><br>  
            <a href="https://devdocs.prestashop-project.org/1.7/basics/installation/system-requirements/" target="_blank">Vérifiez ici les prérequis pour la version 1.7.x</a><br><br>
            
            <div class="info-box">
                <b>Votre hébergement tourne actuellement en PHP :</b><br>
                <?php echo phpversion(); ?>

                <div class="extensions">
                    <?php 
                        $extensions = ['CURL','DOM','Fileinfo','GD','Iconv','Intl','JSON','Mbstring','OpenSSL','DOM','PDO','PDO_MYSQL','SimpleXML','Zip'];
                        echo "<ul>";
                        foreach ($extensions as $extension) {
                            echo "<li>";
                            if (extension_loaded($extension)) {
                                echo "<span class='icon-ok'>✔</span> $extension";
                            } else {
                                echo "<span class='icon-not-ok'>✖</span> $extension";
                            }
                            echo "</li>"; 
                        }
                        echo "</ul>";
                    ?>
                </div>
            </div>
        </p>
        
        <div class="footer">
            <p>&copy; 2023 PROGERANCE.COM. - Ce script est sous licence - Academic Free License AFL 3.0</p>
            <p>Soutenez notre travail : <a href="https://www.buymeacoffee.com/progerance">Offrez-moi un café</a></p>
        </div>	
    </div>
</body>
</html>
