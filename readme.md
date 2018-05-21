# General workflow

This application fetch data (about articles and images) from Newscoop API, and push it to rabbitmq queue.
Special consumer is processing item (article/image) and save it in file system (public/ninjs) as a json files. 
Imported files can be used by Superdesk ingest (or any other system supporting ninjs).

## Commands
```
  newscoop:import-and-save-to-ninjs  Imports newscoop articles and save it to json files with content in ninjs format.
  newscoop:import-brasil247          Imports newscoop articles from brasil247 and save it to json.
  newscoop:import-brasil247:images   Imports newscoop images from brasil247 and save it to json (ninjs).
  rabbitmq:consumer newscoop_import  Process items in queue.
```
