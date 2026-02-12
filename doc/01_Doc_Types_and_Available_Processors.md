# Document Types and Available PDF Processors
## Document Types
This bundle introduces 2 new document types:

| Type           | Description                                                                                                                                                 | 
|----------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [PrintPage](./02_Print_Documents.md#printpage)      | Like pages, but specialized for print (PDF preview, rendering options, ...)                                                                                 | 
| [PrintContainer](./02_Print_Documents.md#printcontainer) | Organizing print pages in chapters and render them all together.                                                                                            | 

## Available PDF Processors

| Name           | Description                                                                                                                                                | 
|----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [Gotenberg](https://gotenberg.dev/) | A Docker service with Chromium and LibreOffice support                                                                                                     | 
| [PDF Reactor](https://www.pdfreactor.com/) | A REST solution for rendering complex catalogs, please visit the official website for further information. Currently OpenDXP supports PDFreactor 10, 11, 12 | 
| [Dompdf](https://github.com/dompdf/dompdf) | A PHP-based HTML to PDF converter. |

 > For details on how to install and configure these processors, please see [Additional Tools Installation](https://docs.opendxp.io/docs/core-framework/Installation_and_Upgrade/System_Setup_and_Hosting/Additional_Tools_Installation) page in the Core.

You can also add your own Processors as long as they extend the abstract OpenDxp\Bundle\WebToPrintBundle\Processor class. Set the full class name in the web2print settings, for example "App\Web2Print\Processor\WkHtmlToPdf".
