# Master-Nodelist

This is a wordpress plugin written in anticipation of getting a particular contract.  I didn't get the contract, but that wasn't the plugin's fault.  Other events intervened, and ... well, you know ... sh*t happens.

I had gone ahead and written the plugin because of the import/export spreadsheet aspect of it.  I had not worked with [PHPSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) before and was eager to see what I could do with it.  I was pleasantly surprised about how easy it was to work with, in spite of the somewhat spotty coverage of the documentation.  Actually, it has far better documentation than I had originally expected, but it never seemed to have what I was looking for in one place.  And the examples all seemed a little simplistic, not a real world example in any of them.  In that vein, the code reading the spreadsheet is in [classes/Form/Workbook.php](https://github.com/RichardCoffee/Master-Nodelist/blob/master/classes/Form/Workbook.php), in the import_nodelist method, and the code writing to a spreadsheet is in [classes/Plugin/Nodelist.php](https://github.com/RichardCoffee/Master-Nodelist/blob/master/classes/Plugin/Nodelist.php), in the write_spreadsheet method.  The latter file also contains code emailing the exported spreadsheet to a user, in the email_spreadsheet method.

In the end, I decided to go ahead and make the code available here on github.  If you find that the code here helps you out on one of your projects, I just ask that you add a link to this repository in a code comment within your project.  It is something I try to do when other people's code helps me out.


