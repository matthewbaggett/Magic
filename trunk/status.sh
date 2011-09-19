#!/bin/bash
svn status . --ignore-externals;
svn status plugins/*;
svn status application/*;
