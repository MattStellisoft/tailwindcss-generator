# Tailwind CSS Generator

Generate your TailwindCSS output directly in your PHP application.

## The Problem

I run a web development platform [Stellify](https://stellisoft.com) that allows users to develop Laravel applications in the cloud. This includes allowing the user to style interfaces using regualar CSS via inline styles and/ or defined classes. We also promote use of the popular TailwindCSS utility-first framework. Whilst working in development I saw no problem in making use of the CDN to import the TailwindCSS classes, all of which would be required in order to give the user the freedom to style their interfaces as they want to. 

Problems arise when moving from development into production, at which point we don't want to use the CDN due its bundle size and the resulting latency. Ordinarily, Tailwind's build step would scan the templates in your project path to determine the classes that are in use in order to produce a much smaller, static CSS file. The snag is, Stellify doesn't generate files. What is does is construct interfaces from JSON that's stored in a database, meaning there are no files for the Tailwind CLI to scan. There is a potential workaround for dealing with dynamic classes that involves using a script that involves PostCSS [see here](https://github.com/tailwindlabs/tailwindcss/discussions/14636#discussioncomment-10895673). Unfortunately, that's not going to work for Stellify but it did give me an idea.

## The Solution

My solution is to store the TailwindCSS classes in a database table, query the table using the classes that are in use for any given page, then build out the CSS markup using the data returned from the query. The generated classes will simply be appended to the preflight CSS. Not only does this solve my problem of dealing with dynamic classes, it also opens up other potential benefits such as:

- The choice to either place the generated classes into a CSS output file that can fetched from the server or to dynamically build the CSS on each request, then either serve it from an endpoint or inject it directly into the webpage. Whilst this certainly wouldn't be optimal in production, it could be useful in development/ testing.
- Classes can be easily overridden using the query that fetches the classes from the database.
- The core classes can be built upon in an organised fashion, other fields can be added to the table that houses the classes that assist with filtering and assigning classes to various entities such as pages or user profiles.

That said, there are some perceived downsides:

- The database table containing class definitions will need to be maintained and kept up-to-date with the latest version of TailwindCSS.
- You lose ability to customise your configuration using Tailwind CLI

## User Guide

- Run the generate-tailwind-sql.php script using the command `php generate-tailwind-sql.php`. This will generate the insert query and place it in a file named tailwind-insert.sql.
- Create a table in your database and make sure it has a name field (VARCHAR), to store the classname a rule field (VARCHAR), to store any rules that may apply to the selector and a data column (JSON), to store the styles that apply to the class.
- Execute the tailwind-insert.sql query using the CLI or your DBMS to populate the table's name and data fields.
- Create a method in your application that performs the steps needed to generate the CSS as shown in the laravel-example.php file.
