# ![checkdomain GmbH](https://www.checkdomain.de/img/logos/cd-github-logo.png "Sponsored by checkdomain") UploadManagerBundle
Dieses Bundle stellt ein Service und einen FormType für asynchrone Uploads zur Verfügung. Dabei werden hinzugefügte und
gelöschte Verzeichnisse mittels Ajax temporär vermerkt und erst beim Speichern des Formulars mit dem Zielverzeichnis
synchronisiert.

## Installation
Befolge folgende Schritte, um das Bundle in deiner Symfony-Umgebung zu installieren.

### 1. Schritt
Füge die folgende Zeile zu deiner ```composer.json``` hinzu:

```json
"require" :  {
    // ...
    "checkdomain/upload-manager-bundle": "dev-master",
}
````

### 2. Schritt
Führe ein ```composer update``` aus, um die Pakete neu zu laden.

### 3. Schritt
Registriere das Bundle mit folgender Codezeile:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
            // ...
            new Checkdomain\UploadManagerBundle\CheckdomainUploadManagerBundle(),
    );
    // ...
}
```

### 4. Schritt
Für den FormType solltest du die ```fields.html.twig``` zu deiner twig-Konfiguration in ```app/config/config.php``` hinzufügen.

```yaml
# Twig Configuration
twig:
  form:
    resources:
      - 'CheckdomainUploadManagerBundle:Form:fields.html.twig'
```

### 5. Schritt
Für die Standard-Aufgaben, wie Datei löschen und wiederherstellen, liefert dieses Bundle bereits einen Controller.
Um diesen zu nutzen, muss folgende Route in der ```app/config/routing.yml``` eingebunden werden.

```yaml
checkdomain_upload_manager:
    resource: "@CheckdomainUploadManagerBundle/Resources/config/routing.yml"
```

### 6. Schritt (optional für development)
In einer dev-Umgebung kann es hilfreich sein das im Bundle vorhandene Beispiel einzubinden. Dazu einfach in der
```app/config/routing_dev.yml``` folgende Route hinzufügen:

```yaml
checkdomain_upload_manager_dev:
    resource: "@CheckdomainUploadManagerBundle/Resources/config/routing_dev.yml"
```

## Konfiguration
Folgende Konfigurationen stehen dir zur Verfügung:

```yaml
checkdomain_upload_manager:
  write_to: '%kernel.root_dir%/../web'
  upload_path: 'upload'
  temp_upload_path: '%upload_manager.upload_path%/temp'
  temp_upload_lifetime: 10800
  tidy_up_likelihood: 10
```

- **write_to** <br /> Sollte auf ein public-Verzeichnis (Standard "web") zeigen. Bildet das Hauptverzeichnis für die
Optionen ```upload_path``` und ```temp_upload_path```
- **upload_path** <br /> In diesem Order werden die Uploads abgelegt
- **temp_upload_path** <br /> In diesem Ordner werden temporäre Uploads abgelegt
- **temp_upload_lifetime** <br /> Gibt an, wie lange die temporären Dateien vorbehalten bleiben sollen
- **tidy_up_likelihood** <br /> Gibt an, in welcher Wahrscheinlichkeit bei einem Request das temporäre Verzeichnis
aufgeräumt werden soll. <br /> (Wahrscheinlichkeit 1 zu x)

## Anwendung
In der Regel benötigen wir ein Formular und einen Controller mit zwei Actions. Einmal eine Action zum Anzeigen und
Speichern des Formulars und eine Action, die uns unsere Dateien validiert und speichert.

### 1. Formular anlegen
Zuerst erstellen wir unser Formular, welches wir ```test``` nennen möchten. Das Formular soll die Option
```upload_url``` benötigen, mit dieser wir später unsere Upload-Action an das ```upload```-Feld übergeben können.

```php
<?php // /src/Acme/DemoBundle/Form/Type/TestType.php

namespace Acme\DemoBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TestType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'upload_url'
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('files_unique_id', 'upload', array(
            'upload_url' => $options['upload_url'],
            'upload_dir' => NULL,
            'label'  => 'Your documents',
        ));
    }
    
    public function getName()
    {
        return 'test';
    }
}
```

Unser Upload-Feld haben wir ```files_unique_id``` gennant, denn dieses Feld gibt uns nicht die hochgeladenen Dateien
direkt, sondern die Referenz (eine ID) zu diesen zurück.

### 2. Controller anlegen
Unser Controller soll in der ```indexAction``` das Formular anzeigen.

```php
<?php /src/Acme/DemoBundle/Controller/TestController.php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function indexAction()
    {
        // Create test form
        $form = $this->createForm(new \Acme\DemoBundle\Form\Type\TestType(), NULL, array(
            // controller upload action
            'upload_url' => $this->generateUrl('acme_demo_test_upload')
        ));
        
        // Render the view
        return $this->render('AcmeDemoBundle:Test:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    public function uploadAction()
    {
    
    }
}
```

Wir haben in dem Beispiel bereits über eine Route eine URL für unsere ```upload_url```-Option erstellt und übergeben.
Diese zeigt auf unsere ```uploadAction```.

### 3. View anlegen
In unserem Template geben wir das Formular aus und laden das im Bundle vorhandene JavaScript und beispielhafte CSS.

```twig
{% javascripts
    'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'
    '@CheckdomainUploadManagerBundle/Resources/public/js/*'
%}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}

{% stylesheets
    '@CheckdomainUploadManagerBundle/Resources/public/css/*'
%}
    <link href="{{ asset_url }}" rel="stylesheet" media="screen" />
{% endstylesheets %}

<h1>Test-Upload</h1>

<form action="" method="post">
    {{ form_row(form) }}
    <hr />
    <input type="submit" value="Save" />
</form>
```

**Hinweis:** In unserem Beispiel nutzen wir *Assetic*.

### 4. Dateien validieren
Dateien, die nun zu unserem Upload-Manager-Feld hinzugefügt werden, werden sofort mit Ajax an den Server gesendet.
Wir lassen diesen Request auf unsere ```uploadAction``` laufen, wo wir nun die Datei wie folgt validieren könnten.

```php
    public function uploadAction()
    {
        // Get upload_manager service
        $upload_manager = $this->get('upload_manager');
        
        // Get instance with request post data (always "unqiue_id")
        $upload_manager->getInstance($this->getRequest()->get('unique_id'));
        
        // Set constraints to validate
        $upload_manager->setConstraints(array(
            new \Symfony\Component\Validator\Constraints\NotNull(),
            new \Symfony\Component\Validator\Constraints\File(array(
                'maxSize' => '200k'
            )),
            new \Symfony\Component\Validator\Constraints\Image()
        ));
        
        // Set upload directory, if not set
        if (!$upload_manager->getDestinationDirectory())
        {
            $upload_manager->setDestinationDirectory('test_documents/' . time());
        }
        
        // Try to add a file and build response array
        try {
            $response = array(
                'data' => $upload_manager->addFile($this->getRequest()->files->get('file'))
            );
        } catch (\Checkdomain\UploadManagerBundle\Exception\ValidatorException $e) {
            $response = array(
                'errors' => $e->getErrorMessages()
            );
        }
        
        // Create a json response
        return new Response(json_encode($response));
    }
```

Mit der Methode ```setDestinationDirectory``` setzen wir hier das Zielverzeichnis für unsere Datei. Dieses Verzeichnis
wird zwischengespeichert, wodurch wir beim Speichern des Formulars später diese Angabe nicht erneut machen zu brauchen.
Möchten wir ein vorhandenes Verzeichnis bearbeiten, somit wohl auch ein ausgefülltes Formular, können wir in den
meisten Fällen diese Angabe bereits in der ```indexAction``` vornehmen. Wenn wir allerdings, wie in diesem Fall, einen
komplett neuen Eintrag anlegen möchten, kennen wir unseren Zielordner evtl. noch nicht, da wir die ID des Eintrages im
Ordner stehen haben möchten.

Wichtig ist, dass die Response immer so aufgebaut wird, wie in diesem Beispiel, denn so erwartet es das Javascript.

### 5. Formular validieren und Dateien synchronisieren
Wenn man das Formular abschickt, muss natürlich dieses noch validiert werden. Wenn die Validation erfolgreich ist,
müssen die zuvor hochgeladenen Dateien noch mit dem Zielverzeichnis (welches in unserem Beispiel ja zwischengespeichert
ist) synchronisiert werden. Dafür ändern wir unsere ```indexAction``` wie folgt:

```php
    public function indexAction()
    {
        // Create test form
        $form = $this->createForm(new \Acme\DemoBundle\Form\Type\TestType(), NULL, array(
            // controller upload action
            'upload_url' => $this->generateUrl('acme_demo_test_upload')
        ));
        
        // We got some POST data
        if ($this->getRequest()->isMethod('POST'))
        {
            // Bind request to the form
            $form->bind($this->getRequest());
            
            // Validate form
            if ($form->isValid())
            {
                // Get upload_manager
                $uploadmanager = $this->get('upload_manager');
                
                // Get instance by post data (field name from "test type")
                $uploadmanager->getInstance($form->get('files_unique_id')->getData());
                
                // Synchronise uploaded and deleted with existing files
                $uploadmanager->synchronise();
            }
        }
        
        // Render the view
        return $this->render('AcmeDemoBundle:Test:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
```
