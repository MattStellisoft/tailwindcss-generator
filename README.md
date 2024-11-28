# Tailwind CSS Generator

Generate TailwindCSS classes dynamically from within your application.

## Problem

I oversee [Stellify](https://stellisoft.com), a web development (IDE) platform that allows users to build and serve Laravel applications in the cloud. The platform allows users to style their interfaces using regular CSS however, due to its popularity, we also promote using TailwindCSS. When using the development environment, we use the CDN to import the TailwindCSS classes. That said, when moving from development into production, we don't want to use the CDN due its bundle size and the resulting latency. 

Stellify doesn't generate files and is multi-tenancy. What it does is creates JSON configuration that gets stored in a database against a project identifier, meaning there are no files for the Tailwind CLI to scan, neither is there a directory to store generated files for each project. So although there is an existing workaround for dealing with dynamic classes, one that involves using a script utilising PostCSS [see here](https://github.com/tailwindlabs/tailwindcss/discussions/14636#discussioncomment-10895673), unfortunately, that's not going to work for Stellify.

## Solution

Why not store the TailwindCSS classes, extract the classes that are in use for any given page, then build out the CSS markup using the data? The generated classes will simply be appended to the preflight styles at which point we can pipe the resulting output into a file or embed it directly in a webpage. Not only does this solve my problem of handling dynamic classes, it also creates other potential benefits such as:

- You have the choice to either place the generated classes into a CSS file that can be fetched from the server or to dynamically build the CSS on each request, then you can either serve it as a file or inject the classes directly into your webpage. Whilst the latter option certainly wouldn't be optimal in production, it could be of use during development/ testing.
- If you choose to store the classes in a database, this creates options such as allowing for multi-tenancy. Classes can be easily overridden and/ or filtered using queries. 
- Classes found in plugins (such as the Typography plugin) can be (dynamically) appended to the output file in exactly the same way as the core classes.

## User Guide

### Using JSON

- Get the contents of `tailwind-classes.json` and decode it as JSON in your application.
- Create a method in your application that performs the steps needed to generate the CSS as demonstrated in the laravel-example.php file.

### Using a Database

- Create a table in your database (in the example code it's named `tailwind-classes`) and make sure it has a `name` field (VARCHAR) to store the classname; A `rule` field (VARCHAR) to store any rules that may apply to the selector; A `category` field (VARCHAR) used to group classes by category and a `data` field (JSON), to store the styles that apply to a given class.
- Run the generate-tailwind-sql.php script using the command `php generate-tailwind-sql.php`. This will generate the insert query and place it in a current directory as `tailwind-insert.sql`.
- Execute the tailwind-insert.sql query using your CLI\ DBMS to populate the `tailwind-classes` table (be sure to escape backslashes).
- Create a method in your application that performs the steps needed to generate the CSS as demonstrated in the laravel-example.php file.
