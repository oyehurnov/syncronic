**SQL Dump Parser Control Panel**

Database content parser + web interface for management, implemented using the Yii 2 framework and its widgets for displaying data.

- The interface allows to select a specific database or all databases from the list. 
- The list is formed from files from a specified folder.
- Implemented a possibility to add and delete database files.
- At the output, we get an xml news file from the selected database (a download link is provided in the interface).
- The news consists of a title and text.
- It is possible to get a result for several databases combined into one file.
- The content from the databases at the output is cleaned of links and images while preserving basic formatting.
