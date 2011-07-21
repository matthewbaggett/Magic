#!/bin/bash
touch update.lock
svn cleanup
svn update application/* 
svn update plugins/*
svn update . --ignore-externals
rm update.lock
