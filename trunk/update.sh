#!/bin/bash
svn cleanup
svn update application/* 
svn update plugins/*
svn update . --ignore-externals
