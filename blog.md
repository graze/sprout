# Sprout 🌱

Seeding your data!

## Problem

Here at graze we have many different projects in many different languages.
From monoliths to micro-services, each project has their own method of seeding data that is close but
slightly different.

## Solution: Sprout 🌱

[![xkcd](https://imgs.xkcd.com/comics/standards.png)](https://xkcd.com/927/)

We split out the core of our monolith seeding code to become the basis of the new application.
This mainly dealt with `.sql` files, providing a limited scope for the development.

It also gave us the requirements of handling a lot of data and to be as quick as possible.

We also wanted to implement the ability to categorise the seed data.
This would allow us to seed, for example, static, operational and testing data individually.
