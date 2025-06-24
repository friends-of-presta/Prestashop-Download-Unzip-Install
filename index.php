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
    </style>
</head>
<body>
    <div class="container">
        <h2>PrestaShop Download | Unzip | Install</h2>
		<p>Ce script a été créé pour aider la communauté des développeurs PrestaShop. Il facilite le téléchargement et l'installation des différentes versions de PrestaShop directement depuis le dépôt GitHub.</p>
        <?php
		function getPrestaShopVersions() {
			$url = "https://api.github.com/repos/PrestaShop/PrestaShop/tags";
			$opts = [
				"http" => [
					"method" => "GET",
					"header" => "User-Agent: request"
				]
			];

			$context = stream_context_create($opts);
			$json = file_get_contents($url, false, $context);
			$tags = json_decode($json, true);

			$versions = [];
			foreach ($tags as $tag) {
				$versions[] = $tag['name'];
			}

			return $versions;
		}

		function downloadPrestaShop($version) {
			$url = "https://github.com/PrestaShop/PrestaShop/releases/download/{$version}/prestashop_{$version}.zip";
			$zipFile = "prestashop_{$version}.zip";

			file_put_contents($zipFile, fopen($url, 'r'));

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

        function checkExtension ($extension) {
            if (extension_loaded($extension)) {
                return "<b><font color=green>extension $extension : OK</font><br></b>";
            } else {
                return "<b><font color=red>extension $extension : NOK</font><br></b>";
            }
        }

        function endScript() {
            echo "Fin du script";
            exit();
        }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['version']) && empty($_POST['unzip']) && empty($_POST['redirect'])) {
        $zipFile = downloadPrestaShop($_POST['version']);
        echo "<div>Téléchargement de {$zipFile} terminé.</div>";
        echo "<div>Voulez-vous décompresser le fichier téléchargé ?</div>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='version' value='{$_POST['version']}'>";
        echo "<input type='hidden' name='unzip' value='{$zipFile}'>";
        echo "<input type='submit' style='background-color: green; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;' value='OUI' name='reponse'>";
        echo "<input type='submit' style='background-color: red; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;' value='NON' name='reponse'>";
        echo "</form>";
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
    $versions = getPrestaShopVersions();
    echo "<form method='post'>";
    echo "<select name='version'>";
    foreach ($versions as $version) {
        echo "<option value='{$version}'>{$version}</option>";
    }
    echo "</select>";
    echo "<input type='submit' value='Télécharger'>";
    echo "</form>";
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
