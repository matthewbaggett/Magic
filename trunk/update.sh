#!/bin/bash
touch update.lock
svn cleanup
svn update application/* 
svn update plugins/*
svn update . --ignore-externals
#rm application/*/temp/html_cache -Rf
rm update.lock
