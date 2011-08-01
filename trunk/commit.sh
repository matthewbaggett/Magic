#!/bin/bash
svn cleanup
svn commit -m '$1' application/* 
svn commit -m '$1' plugins/*
svn commit -m '$1' . 
